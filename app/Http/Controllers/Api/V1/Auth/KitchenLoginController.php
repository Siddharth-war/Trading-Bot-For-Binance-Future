<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\ChefBranch;
use App\User;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class KitchenLoginController extends Controller
{
    public function __construct(
        private User  $user,
        private ChefBranch $chefBranch,
        private Branch $branch
    ){}
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email_or_phone' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if (is_numeric($request['email_or_phone'])) {
            $data = [
                'phone' => $request['email_or_phone'],
                'password' => $request->password,
                'is_active' => 1,
                'user_type' => 'kitchen',
            ];

        } elseif (filter_var($request['email_or_phone'], FILTER_VALIDATE_EMAIL)) {
            $data = [
                'email' => $request['email_or_phone'],
                'password' => $request->password,
                'is_active' => 1,
                'user_type' => 'kitchen',
            ];

        } else {
            $data = [];
        }

        $userId = $request['email_or_phone'];

        $user = $this->user->where('is_active', 1)
            ->where('user_type', 'kitchen')
            ->where(function ($query) use ($userId) {
                $query->where(['email' => $userId])->orWhere('phone', $userId);
            })->first();

        $maxLoginHit = Helpers::get_business_settings('maximum_login_hit') ?? 5;
        $tempBlockTime = Helpers::get_business_settings('temporary_login_block_time') ?? 600; // seconds

        if (isset($user)) {

            $chefBranch = $this->chefBranch->where('user_id', $user->id)->first();
            $branch = $this->branch->where(['id' => $chefBranch->branch_id])->first();

            if (!isset($branch)){
                $errors = [];
                $errors[] = ['code' => 'auth-001', 'message' => translate('Branch deleted, please contact with admin.')];
                return response()->json(['errors' => $errors], 401);
            }

            if(isset($user->temp_block_time ) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
                $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

                $errors = [];
                $errors[] = ['code' => 'login_block_time',
                    'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                ];
                return response()->json([
                    'errors' => $errors
                ], 403);
            }

            if (auth()->attempt($data)) {
                $token = auth()->user()->createToken('KitchenChefAuth')->accessToken;
                return response()->json([
                    'user' => auth()->user(),
                    'token' => $token,
                    'message' => translate('Successfully login.')
                ], 200);
            }
            else{
                if(isset($user->temp_block_time ) && Carbon::parse($user->temp_block_time)->DiffInSeconds() <= $tempBlockTime){
                    $time= $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

                    $errors = [];
                    $errors[] = [
                        'code' => 'login_block_time',
                        'message' => translate('please_try_again_after_') . CarbonInterval::seconds($time)->cascade()->forHumans()
                    ];
                    return response()->json([
                        'errors' => $errors
                    ], 403);
                }
                if($user->is_temp_blocked == 1 && Carbon::parse($user->temp_block_time)->DiffInSeconds() >= $tempBlockTime){

                    $user->login_hit_count = 0;
                    $user->is_temp_blocked = 0;
                    $user->temp_block_time = null;
                    $user->updated_at = now();
                    $user->save();
                }

                if($user->login_hit_count >= $maxLoginHit &&  $user->is_temp_blocked == 0){
                    $user->is_temp_blocked = 1;
                    $user->temp_block_time = now();
                    $user->updated_at = now();
                    $user->save();

                    $time = $tempBlockTime - Carbon::parse($user->temp_block_time)->DiffInSeconds();

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
            $user->login_hit_count += 1;
            $user->temp_block_time = null;
            $user->updated_at = now();
            $user->save();
        }

        $errors = [];
        $errors[] = ['code' => 'auth-001', 'message' => translate('Invalid credential.')];
        return response()->json(['errors' => $errors], 401);
    }

    /**
     * @param Request $request
     * @return Response|Application|ResponseFactory
     */
    public function logout(Request $request): Response|Application|ResponseFactory
    {
        $token = $request->user()->token();
        $token->revoke();

        return response(['message' => translate('You have been successfully logged out!')], 200);
    }

}
