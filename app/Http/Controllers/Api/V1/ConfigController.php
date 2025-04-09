<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\Currency;
use App\Model\SocialMedia;
use App\Model\TimeSchedule;
use App\Models\LoginSetup;
use App\Traits\HelperTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    use HelperTrait;
    private $map_key;
    public function __construct(
        private Currency        $currency,
        private Branch          $branch,
        private TimeSchedule    $timeSchedule,
        private BusinessSetting $businessSetting,
        private LoginSetup      $loginSetup,
    ){
        $this->map_key = Helpers::get_business_settings('map_api_client_key');
    }

    /**
     * @return JsonResponse
     */
    public function configuration(): JsonResponse
    {
        $digitalPayment = Helpers::get_business_settings('digital_payment');

        $publishedStatus = 0; // Set a default value
        $paymentPublishedStatus = config('get_payment_publish_status');
        if (isset($paymentPublishedStatus[0]['is_published'])) {
            $publishedStatus = $paymentPublishedStatus[0]['is_published'];
        }

        $activeAddonPaymentLists = $publishedStatus == 1 ? $this->getPaymentMethods() : $this->getDefaultPaymentMethods();

        $digitalPaymentStatusValue = Helpers::get_business_settings('digital_payment');

        $digitalPaymentInfos = array(
            'digital_payment' => $digitalPayment['status'] == 1 ? 'true' : 'false',
            'plugin_payment_gateways' =>  $publishedStatus ? "true" : "false",
            'default_payment_gateways' =>  $publishedStatus ? "false" : "true"
        );

        $currencySymbol = $this->currency->where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;
        $cod = Helpers::get_business_settings('cash_on_delivery');

        $deliveryConfig = Helpers::get_business_settings('delivery_management');
        $deliveryManagement = array(
            "status" => (int)$deliveryConfig['status'],
            "min_shipping_charge" => (float)$deliveryConfig['min_shipping_charge'],
            "shipping_per_km" => (float)$deliveryConfig['shipping_per_km'],
        );

        $playStoreConfig = Helpers::get_business_settings('play_store_config');
        $appStoreConfig = Helpers::get_business_settings('app_store_config');

        $schedules = $this->timeSchedule->select('day', 'opening_time', 'closing_time')->get();
        $branchPromotion = $this->branch->with('branch_promotion')->where(['branch_promotion_status' => 1])->get();

        $google = Helpers::get_business_settings('google_social_login') ?? 0;
        $facebook = Helpers::get_business_settings('facebook_social_login') ?? 0;

        $cookiesConfig = Helpers::get_business_settings('cookies');
        $cookies_management = array(
            "status" => (int)$cookiesConfig['status'],
            "text" => $cookiesConfig['text'],
        );

        $offlinePayment = Helpers::get_business_settings('offline_payment');
        $apple = Helpers::get_business_settings('apple_login');
        $appleLogin = array(
            'login_medium' => $apple['login_medium'],
            'status' => 0,
            'client_id' => $apple['client_id']
        );

        $firebaseOTPVerification = Helpers::get_business_settings('firebase_otp_verification');

        $emailVerification = (int) Helpers::get_login_settings('email_verification') ?? 0;
        $phoneVerification = (int) Helpers::get_login_settings('phone_verification') ?? 0;

        $status = 0;
        if ($emailVerification == 1) {
            $status = 1;
        } elseif ($phoneVerification == 1) {
            $status = 1;
        }

        $customerVerification = [
            'status' => $status,
            'phone'=> $phoneVerification,
            'email'=> $emailVerification,
            'firebase'=> (int) $firebaseOTPVerification['status'],
        ];

        $loginOptions = Helpers::get_login_settings('login_options');
        $socialMediaLoginOptions = Helpers::get_login_settings('social_media_for_login');

        $customerLogin = [
            'login_option' => $loginOptions,
            'social_media_login_options' => $socialMediaLoginOptions
        ];

        $maintenanceMode = $this->checkMaintenanceMode();

        return response()->json([
            'restaurant_name' => Helpers::get_business_settings('restaurant_name'),
            'restaurant_phone' => Helpers::get_business_settings('phone'),
            'restaurant_open_time' => Helpers::get_business_settings('restaurant_open_time'),
            'restaurant_close_time' => Helpers::get_business_settings('restaurant_close_time'),
            'restaurant_schedule_time' => $schedules,
            'restaurant_logo' => Helpers::get_business_settings('logo'),
            'restaurant_address' => Helpers::get_business_settings('address'),
            'restaurant_email' => Helpers::get_business_settings('email_address'),
            'restaurant_location_coverage' => $this->branch->where(['id' => 1])->first(['longitude', 'latitude', 'coverage']),
            'minimum_order_value' => (float) Helpers::get_business_settings('minimum_order_value'),

            'base_urls' => [
                'product_image_url' => asset('storage/app/public/product'),
                'customer_image_url' => asset('storage/app/public/profile'),
                'banner_image_url' => asset('storage/app/public/banner'),
                'category_image_url' => asset('storage/app/public/category'),
                'category_banner_image_url' => asset('storage/app/public/category/banner'),
                'review_image_url' => asset('storage/app/public/review'),
                'notification_image_url' => asset('storage/app/public/notification'),
                'restaurant_image_url' => asset('storage/app/public/restaurant'),
                'delivery_man_image_url' => asset('storage/app/public/delivery-man'),
                'chat_image_url' => asset('storage/app/public/conversation'),
                'promotional_url' => asset('storage/app/public/promotion'),
                'kitchen_image_url' => asset('storage/app/public/kitchen'),
                'branch_image_url' => asset('storage/app/public/branch'),
                'gateway_image_url' => asset('storage/app/public/payment_modules/gateway_image'),
                'payment_image_url' => asset('public/assets/admin/img/payment'),
                'cuisine_image_url' => asset('storage/app/public/cuisine'),
            ],
            'currency_symbol' => $currencySymbol,
            'delivery_charge' => (float) Helpers::get_business_settings('delivery_charge'),
            'delivery_management' => $deliveryManagement,
            'branches' => $this->branch->all(['id', 'name', 'email', 'longitude', 'latitude', 'address', 'coverage', 'status', 'image', 'cover_image', 'preparation_time']),
            'email_verification' => (boolean) Helpers::get_business_settings('email_verification') ?? 0,
            'phone_verification' => (boolean) Helpers::get_business_settings('phone_verification') ?? 0,
            'currency_symbol_position' => Helpers::get_business_settings('currency_symbol_position') ?? 'right',
            'country' => Helpers::get_business_settings('country') ?? 'BD',
            'self_pickup' => (boolean) Helpers::get_business_settings('self_pickup') ?? 1,
            'delivery' => (boolean) Helpers::get_business_settings('delivery') ?? 1,
            'play_store_config' => [
                "status" => isset($playStoreConfig) && (boolean)$playStoreConfig['status'],
                "link" => isset($playStoreConfig) ? $playStoreConfig['link'] : null,
                "min_version" => isset($playStoreConfig) && array_key_exists('min_version', $appStoreConfig) ? $playStoreConfig['min_version'] : null
            ],
            'app_store_config' => [
                "status" => isset($appStoreConfig) && (boolean)$appStoreConfig['status'],
                "link" => isset($appStoreConfig) ? $appStoreConfig['link'] : null,
                "min_version" => isset($appStoreConfig) && array_key_exists('min_version', $appStoreConfig) ? $appStoreConfig['min_version'] : null
            ],
            'social_media_link' => SocialMedia::orderBy('id', 'desc')->active()->get(),
            'software_version' => (string)env('SOFTWARE_VERSION') ?? null,
            'decimal_point_settings' => (int) (Helpers::get_business_settings('decimal_point_settings') ?? 2),
            'schedule_order_slot_duration' => (int) (Helpers::get_business_settings('schedule_order_slot_duration') ?? 30),
            'time_format' => (string)(Helpers::get_business_settings('time_format') ?? '12'),
            'promotion_campaign' => $branchPromotion,
            'social_login' => [
                'google' => (integer) $google,
                'facebook' => (integer) $facebook,
            ],
            'wallet_status' => (integer) Helpers::get_business_settings('wallet_status'),
            'loyalty_point_status' => (integer) Helpers::get_business_settings('loyalty_point_status'),
            'ref_earning_status' => (integer) Helpers::get_business_settings('ref_earning_status'),
            'loyalty_point_item_purchase_point' => (float) Helpers::get_business_settings('loyalty_point_item_purchase_point'),
            'loyalty_point_exchange_rate' => (float) (Helpers::get_business_settings('loyalty_point_exchange_rate') ?? 0),
            'loyalty_point_minimum_point' => (float) (Helpers::get_business_settings('loyalty_point_minimum_point') ?? 0),
            'whatsapp' => Helpers::get_business_settings('whatsapp'),
            'cookies_management' => $cookies_management,
            'toggle_dm_registration' => (integer) (Helpers::get_business_settings('dm_self_registration') ?? 0),
            'is_veg_non_veg_active' => (integer) (Helpers::get_business_settings('toggle_veg_non_veg') ?? 0),
            'otp_resend_time' => Helpers::get_business_settings('otp_resend_time') ?? 60,
            'digital_payment_info' => $digitalPaymentInfos,
            'digital_payment_status' => (integer) $digitalPaymentStatusValue['status'],
            'active_payment_method_list' => (integer) $digitalPaymentStatusValue['status'] == 1 ? $activeAddonPaymentLists : [],
            'cash_on_delivery' => $cod['status'] == 1 ? 'true' : 'false',
            'digital_payment' => $digitalPayment['status'] == 1 ? 'true' : 'false',
            'offline_payment' => $offlinePayment['status'] == 1 ? 'true' : 'false',
            'guest_checkout' => (integer) (Helpers::get_business_settings('guest_checkout') ?? 0),
            'partial_payment' => (integer) (Helpers::get_business_settings('partial_payment') ?? 0),
            'partial_payment_combine_with' => (string) Helpers::get_business_settings('partial_payment_combine_with'),
            'add_fund_to_wallet' => (integer) (Helpers::get_business_settings('add_fund_to_wallet') ?? 0),
            'apple_login' => $appleLogin,
            'cutlery_status' => (integer) (Helpers::get_business_settings('cutlery_status') ?? 0),
            'firebase_otp_verification_status' => (integer)($firebaseOTPVerification ? $firebaseOTPVerification['status'] : 0),
            'customer_verification' => $customerVerification,
            'footer_copyright_text' => Helpers::get_business_settings('footer_text'),
            'footer_description_text' => Helpers::get_business_settings('footer_description_text'),
            'customer_login' => $customerLogin,
            'google_map_status' => (integer) (Helpers::get_business_settings('google_map_status') ?? 0),
            'maintenance_mode' => (boolean) Helpers::get_business_settings('maintenance_mode') ?? 0,
            'advance_maintenance_mode' => $maintenanceMode,
        ], 200);
    }

    /**
     * @return array
     */
    private function getPaymentMethods(): array
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')->where('settings_type', 'payment_config')->get();
        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additionalData = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additionalData?->gateway_title,
                    'gateway_image' => $additionalData?->gateway_image
                ];
            }
        }
        return $data;
    }

    /**
     * @return array
     */
    private function getDefaultPaymentMethods(): array
    {
        if (!Schema::hasTable('addon_settings')) {
            return [];
        }

        $methods = DB::table('addon_settings')
            ->whereIn('settings_type', ['payment_config'])
            ->whereIn('key_name', ['ssl_commerz','paypal','stripe','razor_pay','senang_pay','paystack','paymob_accept','flutterwave','bkash','mercadopago'])
            ->get();

        $env = env('APP_ENV') == 'live' ? 'live' : 'test';
        $credentials = $env . '_values';

        $data = [];
        foreach ($methods as $method) {
            $credentialsData = json_decode($method->$credentials);
            $additionalData = json_decode($method->additional_data);
            if ($credentialsData->status == 1) {
                $data[] = [
                    'gateway' => $method->key_name,
                    'gateway_title' => $additionalData?->gateway_title,
                    'gateway_image' => $additionalData?->gateway_image
                ];
            }
        }
        return $data;
    }

    /**
     * @param Request $request
     * @return array|JsonResponse|mixed
     */
    public function direction_api(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'origin_lat' => 'required',
            'origin_long' => 'required',
            'destination_lat' => 'required',
            'destination_long' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json?origin='.$request['origin_lat'].','.$request['origin_long'].'&destination='.$request['destination_lat'].','.$request['destination_long'].'&mode=driving&key='.$this->map_key);
        return $response->json();
    }

    public function deliveryFree(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
        ]);

        if ($validator->errors()->count()>0) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $branch = $this->branch->with(['delivery_charge_setup', 'delivery_charge_by_area'])
            ->where(['id' => $request['branch_id']])
            ->first(['id', 'name', 'status']);

        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        if (isset($branch->delivery_charge_setup) && $branch->delivery_charge_setup->delivery_charge_type == 'distance') {
            unset($branch->delivery_charge_by_area);
            $branch->delivery_charge_by_area = [];
        }

        return response()->json($branch);
    }

}
