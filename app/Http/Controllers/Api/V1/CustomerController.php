<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\CustomerAddress;
use App\Model\EmailVerifications;
use App\Model\Newsletter;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\PhoneVerification;
use App\Model\PointTransitions;
use App\Models\GuestUser;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\MessagingException;


class CustomerController extends Controller
{
    public function __construct(
        private CustomerAddress  $customerAddress,
        private User             $user,
        private PointTransitions $pointTransitions,
        private Newsletter       $newsletter,
        private GuestUser        $guestUser,
        private Order            $order,
        private PhoneVerification  $phoneVerification,
        private EmailVerifications  $emailVerifications,
    )
    {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addressList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'guest_id' => auth('api')->user() ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = (bool)auth('api')->user() ? auth('api')->user()->id : $request['guest_id'];
        $userType = (bool)auth('api')->user() ? 0 : 1;

        $addresses = $this->customerAddress
            ->where(['user_id' => $userId, 'is_guest' => $userType])
            ->latest()
            ->get();

        return response()->json($addresses, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
            'guest_id' => auth('api')->user() ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = (bool)auth('api')->user() ? auth('api')->user()->id : $request['guest_id'];
        $userType = (bool)auth('api')->user() ? 0 : 1;

        if ($request->has('is_default')){
            $this->customerAddress
                ->where(['user_id' => $userId, 'is_guest' => $userType, 'is_default' => 1])
                ->update(['is_default' => 0]);
        }

        $address = $this->customerAddress;
        $address->user_id = $userId;
        $address->is_guest = $userType;
        $address->contact_person_name = $request->contact_person_name;
        $address->contact_person_number = $request->contact_person_number;
        $address->floor = $request->floor;
        $address->house = $request->house;
        $address->road = $request->road;
        $address->address_type = $request->address_type;
        $address->address = $request->address;
        $address->longitude = $request->longitude;
        $address->latitude = $request->latitude;
        $address->is_default = $request['is_default'] ? 1 : 0;
        $address->save();

        return response()->json(['message' => translate('added_success')], 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateAddress(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'contact_person_name' => 'required',
            'address_type' => 'required',
            'contact_person_number' => 'required',
            'address' => 'required',
            'guest_id' => auth('api')->user() ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = (bool)auth('api')->user() ? auth('api')->user()->id : $request['guest_id'];
        $userType = (bool)auth('api')->user() ? 0 : 1;

        if ($request->has('is_default')){
            $this->customerAddress
                ->where(['user_id' => $userId, 'is_guest' => $userType, 'is_default' => 1])
                ->update(['is_default' => 0]);
        }

        $this->customerAddress->where('id', $id)->update([
            'user_id' => $userId,
            'is_guest' => $userType,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_number' => $request->contact_person_number,
            'floor' => $request->floor,
            'house' => $request->house,
            'road' => $request->road,
            'address_type' => $request->address_type,
            'address' => $request->address,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'is_default' => $request['is_default'] ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => translate('update_success')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteAddress(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required',
            'guest_id' => auth('api')->user() ? 'nullable' : 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $userId = (bool)auth('api')->user() ? auth('api')->user()->id : $request['guest_id'];
        $userType = (bool)auth('api')->user() ? 0 : 1;

        if ($this->customerAddress->where(['id' => $request['address_id'], 'user_id' => $userId, 'is_guest' => $userType])->first()) {
            $this->customerAddress->where(['id' => $request['address_id'], 'user_id' => $userId, 'is_guest' => $userType])->delete();
            return response()->json(['message' => translate('successfully removed!')], 200);
        }

        return response()->json(['message' => translate('no_data_found')], 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        $user = $this->user
            ->withCount(['orders', 'wishlist'])
            ->where(['id' => $request->user()->id])
            ->first();

        return response()->json($user, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'f_name' => 'required|string|max:255',
            'l_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $request->user()->id,
            'password' => 'nullable|string|min:6',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'f_name.required' => translate('first_name_required'),
            'l_name.required' => translate('last_name_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        if ($request['password'] != null && strlen($request['password']) > 5) {
            $password = bcrypt($request['password']);
        } else {
            $password = $request->user()->password;
        }

        $user = $this->user->find($request->user()->id);
        if (!$user){
            return response()->json(['message' => translate('User not found')], 200);
        }

        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;

        if ($user->email != $request['email']){
            $user->email_verified_at = null;
            $user->email = $request->email;
        }

        $user->phone = $request->phone;
        $user->image = $request->has('image') ? Helpers::update('profile/', $request->user()->imagee, 'png', $request->file('image')) : $request->user()->image;
        $user->password = $password;
        $user->update();

        return response()->json(['message' => translate('update_success')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFirebaseToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cm_firebase_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = auth('api')->user();
        $guest = $request['guest_id'];

        if (isset($user) && isset($guest)){
            $this->user->where('id', auth('api')->user()->id )->update([
                'cm_firebase_token' => $request['cm_firebase_token'],
                'language_code' => $request->header('X-localization') ?? 'en'
            ]);

            $this->guestUser->where('id', $request['guest_id'])->update([
                'fcm_token' => '@',
            ]);

        }elseif(isset($user)){
            $this->user->where('id', auth('api')->user()->id)->update([
                'cm_firebase_token' => $request['cm_firebase_token'],
                'language_code' => $request->header('X-localization') ?? 'en'
            ]);

        }elseif(isset($guest)){
            $this->guestUser->where('id',  $request['guest_id'])->update([
                'fcm_token' => $request['cm_firebase_token'],
            ]);
        }

        return response()->json(['message' => translate('update_success')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactionHistory(Request $request): JsonResponse
    {
        return response()->json($this->pointTransitions->latest()->where(['user_id' => $request->user()->id])->get(), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function subscribeNewsletter(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $newsLetter = $this->newsletter->where('email', $request->email)->first();
        if (!isset($newsLetter)) {
            $newsLetter = $this->newsletter;
            $newsLetter->email = $request->email;
            $newsLetter->save();

            return response()->json(['message' => translate('Successfully subscribed')], 200);

        } else {
            return response()->json(['message' => translate('Email Already exists')], 400);
        }
    }

    /**
     * @return JsonResponse
     */
    public function lastOrderedAddress(): JsonResponse
    {
        if (!auth('api')->user()){
            return response()->json(['status_code' => 401, 'message' => translate('Unauthorized')], 200);
        }

        $userId = auth('api')->user()->id;

        $defaultAddress = $this->customerAddress
            ->where(['user_id' => $userId, 'is_guest' => 0, 'is_default' => 1])
            ->first();

        if (isset($defaultAddress)){
            return response()->json($defaultAddress, 200);
        }

        $order = $this->order->where(['user_id' => $userId, 'is_guest' => 0])
            ->whereNotNull('delivery_address_id')
            ->orderBy('id', 'DESC')
            ->with('customer_delivery_address')
            ->first();

        if (isset($order) && $order->customer_delivery_address){
            return response()->json($order->customer_delivery_address, 200);
        }

        $lastAddedAddress = $this->customerAddress
            ->where(['user_id' => $userId, 'is_guest' => 0])
            ->first();

        if (isset($lastAddedAddress)){
            return response()->json($lastAddedAddress, 200);
        }

        return response()->json(null, 200);

    }

    public function verifyProfileInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:phone,email',
            'email_or_phone' => 'required',
            'token' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $type = $request['type'];

        if ($type == 'phone'){
            $verificationData =  $this->phoneVerification->where(['phone' => $request['email_or_phone'], 'token' => $request['token']])->first();

            if(!$verificationData){
                return response()->json(['errors' => [
                    ['code' => 'token', 'message' => translate('OTP is not matched!')]
                ]], 403);
            }

            $user = $this->user->find($request->user()->id);
            $user->phone = $request['email_or_phone'];
            $user->is_phone_verified = 1;
            $user->save();

            $verificationData->delete();
            return response()->json(['message' => translate('Phone number is successfully verified')], 200);
        }

        if ($type == 'email'){
            $verificationData =  $this->emailVerifications->where(['email' => $request['email_or_phone'], 'token' => $request['token']])->first();

            if(!$verificationData){
                return response()->json(['errors' => [
                    ['code' => 'token', 'message' => translate('OTP is not matched!')]
                ]], 403);
            }

            $user = $this->user->find($request->user()->id);
            $user->email = $request['email_or_phone'];
            $user->email_verified_at = now();
            $user->save();

            $verificationData->delete();
            return response()->json(['message' => translate('Email is successfully verified')], 200);
        }
    }

    public function fcmSubscribeToTopic(Request $request): JsonResponse|bool|string
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'topic' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $serviceAccountContent = Helpers::get_business_settings('push_notification_service_file_content');
        $serviceAccount = is_array($serviceAccountContent) ? $serviceAccountContent : json_decode($serviceAccountContent, true);
        $factory = (new Factory)->withServiceAccount($serviceAccount);
        $messaging = $factory->createMessaging();

        $token = $request->input('token');
        $topic = $request->input('topic');

        try {
            $messaging->subscribeToTopic($topic, $token);
            return response()->json(['message' => 'Successfully subscribed to topic '. $topic], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
