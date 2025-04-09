<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Mail\DMSelfRegistration;
use App\Model\DeliveryMan;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeliveryManLoginController extends Controller
{
    public function __construct(
        private DeliveryMan $deliveryman,
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function registration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|max:100',
            'l_name' => 'required|max:100',
            'email' => 'required|regex:/(.+)@(.+)\.(.+)/i|unique:delivery_men',
            'phone' => 'required|unique:delivery_men',
            'password' => 'required|min:8',
            'image' => 'required|max:2048',
            'identity_type' => 'required|in:passport,driving_license,nid,restaurant_id',
            'identity_number' => 'required',
            'identity_image' => 'required|max:2048',
            'branch_id' => 'required',
        ], [
            'f_name.required' => translate('First name is required!'),
            'email.required' => translate('Email is required!'),
            'email.unique' => translate('Email is already registered'),
            'phone.required' => translate('Phone is required!'),
            'phone.unique' => translate('Phone number is already registered'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if ($request->has('image')) {
            $imageName = Helpers::upload('delivery-man/', 'png', $request->file('image'));
        } else {
            $imageName = 'def.png';
        }

        $identityImageNames = [];
        if (!empty($request->file('identity_image'))) {
            foreach ($request->identity_image as $img) {
                $identity_image = Helpers::upload('delivery-man/', 'png', $img);
                $identityImageNames[] = $identity_image;
            }
            $identityImage = json_encode($identityImageNames);
        } else {
            $identityImage = json_encode([]);
        }

        $deliveryman = $this->deliveryman;
        $deliveryman->f_name = $request->f_name;
        $deliveryman->l_name = $request->l_name;
        $deliveryman->email = $request->email;
        $deliveryman->phone = $request->phone;
        $deliveryman->identity_number = $request->identity_number;
        $deliveryman->identity_type = $request->identity_type;
        $deliveryman->branch_id = $request->branch_id;
        $deliveryman->identity_image = $identityImage;
        $deliveryman->image = $imageName;
        $deliveryman->is_active = 0;
        $deliveryman->password = bcrypt($request->password);
        $deliveryman->application_status= 'pending';
        $deliveryman->language_code = $request->header('X-localization') ?? 'en';
        $deliveryman->save();

        try{
            $emailServices = Helpers::get_business_settings('mail_config');
            $mail_status = Helpers::get_business_settings('registration_mail_status_dm');
            if(isset($emailServices['status']) && $emailServices['status'] == 1 && $mail_status == 1){
                Mail::to($deliveryman->email)->send(new DMSelfRegistration('pending', $deliveryman->f_name.' '.$deliveryman->l_name, $deliveryman->language_code));
            }
        }catch(\Exception $ex){
            info($ex);
        }

        return response()->json(['message' => translate('deliveryman_added_successfully')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryman = $this->deliveryman->where(['email' => $request->email])->first();

        if (isset($deliveryman) && $deliveryman->application_status != 'approved'){
            $errors = [];
            $errors[] = ['code' => 'auth-001', 'message' => 'Not verified.'];
            return response()->json([
                'errors' => $errors
            ], 401);
        }

        $data = [
            'email' => $request->email,
            'password' => $request->password,
            'is_active' => true
        ];

        $maxLoginHit = Helpers::get_business_settings('maximum_login_hit') ?? 5;
        $tempBlockTime = Helpers::get_business_settings('temporary_login_block_time') ?? 600; // seconds

        if (isset($deliveryman)){
            if (auth('delivery_men')->attempt($data)) {
                if(isset($deliveryman->temp_block_time ) && Carbon::parse($deliveryman->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
                    $time = $tempBlockTime - Carbon::parse($deliveryman->temp_block_time)->DiffInSeconds();

                    $errors = [];
                    $errors[] = ['code' => 'login_block_time',
                        'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ];
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }

                $token = Str::random(120);
                $deliveryman->auth_token = $token;
                $deliveryman->login_hit_count = 0;
                $deliveryman->is_temp_blocked = 0;
                $deliveryman->temp_block_time = null;
                $deliveryman->updated_at = now();
                $deliveryman->save();
                return response()->json(['token' => $token], 200);
            }
            else{
                if(isset($deliveryman->temp_block_time ) && Carbon::parse($deliveryman->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
                    $time = $tempBlockTime - Carbon::parse($deliveryman->temp_block_time)->DiffInSeconds();

                    $errors = [];
                    $errors[] = [
                        'code' => 'login_block_time',
                        'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ];
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }

                if($deliveryman->is_temp_blocked == 1 && Carbon::parse($deliveryman->temp_block_time)->DiffInSeconds() >= $tempBlockTime){
                    $deliveryman->login_hit_count = 0;
                    $deliveryman->is_temp_blocked = 0;
                    $deliveryman->temp_block_time = null;
                    $deliveryman->updated_at = now();
                    $deliveryman->save();
                }

                if($deliveryman->login_hit_count >= $maxLoginHit &&  $deliveryman->is_temp_blocked == 0){
                    $deliveryman->is_temp_blocked = 1;
                    $deliveryman->temp_block_time = now();
                    $deliveryman->updated_at = now();
                    $deliveryman->save();

                    $time= $tempBlockTime - Carbon::parse($deliveryman->temp_block_time)->DiffInSeconds();

                    $errors = [];
                    $errors[] = [
                        'code' => 'login_temp_blocked',
                        'message' => translate('Too_many_attempts. please_try_again_after_'). CarbonInterval::seconds($time)->cascade()->forHumans()
                    ];
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }
            }
            $deliveryman->login_hit_count += 1;
            $deliveryman->temp_block_time = null;
            $deliveryman->updated_at = now();
            $deliveryman->save();
        }

        $errors = [];
        $errors[] = ['code' => 'auth-001', 'message' => 'Invalid credentials.'];
        return response()->json(['errors' => $errors], 401);
    }
}
