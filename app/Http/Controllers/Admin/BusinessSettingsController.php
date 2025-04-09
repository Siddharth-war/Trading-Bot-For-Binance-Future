<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\Currency;
use App\Model\SocialMedia;
use App\Traits\HelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Support\Renderable;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use App\Model\Translation;
use Illuminate\Validation\ValidationException;


class BusinessSettingsController extends Controller
{
    use HelperTrait;

    public function __construct(
        private BusinessSetting $business_setting,
        private Currency        $currency,
        private SocialMedia     $social_media,
        private Branch          $branch
    )
    {
    }

    /**
     * @return Renderable
     */
    public function restaurantIndex(): Renderable
    {
        if (!$this->business_setting->where(['key' => 'minimum_order_value'])->first()) {
            $this->InsertOrUpdateBusinessData(['key' => 'minimum_order_value'], [
                'value' => 1,
            ]);
        }

        return view('admin-views.business-settings.restaurant-index');
    }

    /**
     * @return JsonResponse
     */
    public function maintenanceMode(): JsonResponse
    {
        $mode = Helpers::get_business_settings('maintenance_mode');
        $this->InsertOrUpdateBusinessData(['key' => 'maintenance_mode'], [
            'value' => isset($mode) ? !$mode : 1
        ]);

        $this->sendMaintenanceModeNotification();
        Cache::forget('maintenance');

        if (!$mode) {
            return response()->json(['message' => translate('Maintenance Mode is On.')]);
        }
        return response()->json(['message' => translate('Maintenance Mode is Off.')]);
    }

    /**
     * @param $side
     * @return JsonResponse
     */
    public function currencySymbolPosition($side): JsonResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'currency_symbol_position'], [
            'value' => $side
        ]);
        return response()->json(['message' => translate('Symbol position is ') . $side]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function restaurantSetup(Request $request): RedirectResponse
    {
        if ($request->has('self_pickup')) {
            $request['self_pickup'] = 1;
        }
        if ($request->has('delivery')) {
            $request['delivery'] = 1;
        }
        if ($request->has('dm_self_registration')) {
            $request['dm_self_registration'] = 1;
        }
        if ($request->has('toggle_veg_non_veg')) {
            $request['toggle_veg_non_veg'] = 1;
        }

        if ($request->has('email_verification')) {
            $request['email_verification'] = 1;
            $request['phone_verification'] = 0;
        } elseif ($request->has('phone_verification')) {
            $request['email_verification'] = 0;
            $request['phone_verification'] = 1;
        }

        $request['guest_checkout'] = $request->has('guest_checkout') ? 1 : 0;
        $request['partial_payment'] = $request->has('partial_payment') ? 1 : 0;
        $request['google_map_status'] = $request->has('google_map_status') ? 1 : 0;
        $request['admin_order_notification'] = $request->has('admin_order_notification') ? 1 : 0;

        $this->InsertOrUpdateBusinessData(['key' => 'restaurant_name'], [
            'value' => $request['restaurant_name'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'phone'], [
            'value' => $request['phone'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'country'], [
            'value' => $request['country']
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'time_zone'], [
            'value' => $request['time_zone'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'phone_verification'], [
            'value' => $request['phone_verification']
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'email_verification'], [
            'value' => $request['email_verification']
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'self_pickup'], [
            'value' => $request['self_pickup'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'delivery'], [
            'value' => $request['delivery'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'restaurant_open_time'], [
            'value' => $request['restaurant_open_time'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'restaurant_close_time'], [
            'value' => $request['restaurant_close_time'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'currency'], [
            'value' => $request['currency'],
        ]);

        $currentLogo = $this->business_setting->where(['key' => 'logo'])->first();
        $this->InsertOrUpdateBusinessData(['key' => 'logo'], [
            'value' => $request->has('logo') ? Helpers::update('restaurant/', $currentLogo->value, 'png', $request->file('logo')) : $currentLogo->value
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'phone'], [
            'value' => $request['phone'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'email_address'], [
            'value' => $request['email'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'address'], [
            'value' => $request['address'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'email_verification'], [
            'value' => $request['email_verification'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'footer_text'], [
            'value' => $request['footer_text'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'point_per_currency'], [
            'value' => $request['point_per_currency'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'pagination_limit'], [
            'value' => $request['pagination_limit'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'decimal_point_settings'], [
            'value' => $request['decimal_point_settings']
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'time_format'], [
            'value' => $request['time_format']
        ]);

        $currentFavIcon = $this->business_setting->where(['key' => 'fav_icon'])->first();
        $this->InsertOrUpdateBusinessData(['key' => 'fav_icon'], [
            'value' => $request->has('fav_icon') ? Helpers::update('restaurant/', $currentFavIcon->value, 'png', $request->file('fav_icon')) : $currentFavIcon->value
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'dm_self_registration'], [
            'value' => $request['dm_self_registration'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'toggle_veg_non_veg'], [
            'value' => $request['toggle_veg_non_veg'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'guest_checkout'], [
            'value' => $request['guest_checkout'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'partial_payment'], [
            'value' => $request['partial_payment'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'partial_payment_combine_with'], [
            'value' => $request['partial_payment_combine_with'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'footer_description_text'], [
            'value' => $request['footer_description_text'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'google_map_status'], [
            'value' => $request['google_map_status'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'admin_order_notification'], [
            'value' => $request['admin_order_notification'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'admin_order_notification_type'], [
            'value' => $request['admin_order_notification_type'],
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function mailIndex(): Renderable
    {
        return view('admin-views.business-settings.mail-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function mailConfig(Request $request): RedirectResponse
    {
        $request->has('status') ? $request['status'] = 1 : $request['status'] = 0;
        $this->InsertOrUpdateBusinessData(['key' => 'mail_config'],[
            'value' => json_encode([
                "status" => $request['status'],
                "name" => $request['name'],
                "host" => $request['host'],
                "driver" => $request['driver'],
                "port" => $request['port'],
                "username" => $request['username'],
                "email_id" => $request['email'],
                "encryption" => $request['encryption'],
                "password" => $request['password'],
            ]),
        ]);

        Toastr::success(translate('Configuration updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function mailSend(Request $request): JsonResponse
    {
        $response_flag = 0;
        try {
            $emailServices = Helpers::get_business_settings('mail_config');

            if (isset($emailServices['status']) && $emailServices['status'] == 1) {
                Mail::to($request->email)->send(new \App\Mail\TestEmailSender());
                $response_flag = 1;
            }
        } catch (\Exception $exception) {
            $response_flag = 2;
        }

        return response()->json(['success' => $response_flag]);
    }

    /**
     * @return Renderable
     */
    public function paymentIndex(): Renderable
    {
        $published_status = 0; // Set a default value
        $payment_published_status = config('get_payment_publish_status');
        if (isset($payment_published_status[0]['is_published'])) {
            $published_status = $payment_published_status[0]['is_published'];
        }

        $routes = config('addon_admin_routes');
        $desiredName = 'payment_setup';
        $payment_url = '';

        foreach ($routes as $routeArray) {
            foreach ($routeArray as $route) {
                if ($route['name'] === $desiredName) {
                    $payment_url = $route['url'];
                    break 2;
                }
            }
        }

        $data_values = Setting::whereIn('settings_type', ['payment_config'])
            ->whereIn('key_name', ['ssl_commerz', 'paypal', 'stripe', 'razor_pay', 'senang_pay', 'paystack', 'paymob_accept', 'flutterwave', 'bkash', 'mercadopago'])
            ->get();

        return view('admin-views.business-settings.payment-index', compact('published_status', 'payment_url', 'data_values'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function paymentMethodStatus(Request $request): RedirectResponse
    {
        $request['cash_on_delivery'] = $request->has('cash_on_delivery') ? 1 : 0;
        $request['digital_payment'] = $request->has('digital_payment') ? 1 : 0;
        $request['offline_payment'] = $request->has('offline_payment') ? 1 : 0;

        $this->InsertOrUpdateBusinessData(['key' => 'cash_on_delivery'], [
            'value' => json_encode([
                'status' => $request['cash_on_delivery']
            ])
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'digital_payment'], [
            'value' => json_encode([
                'status' => $request['digital_payment']
            ])
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'offline_payment'], [
            'value' => json_encode([
                'status' => $request['offline_payment']
            ])
        ]);

        Toastr::success(translate('updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function paymentConfigUpdate(Request $request): RedirectResponse
    {
        $validation = [
            'gateway' => 'required|in:ssl_commerz,paypal,stripe,razor_pay,senang_pay,paystack,paymob_accept,flutterwave,bkash,mercadopago',
            'mode' => 'required|in:live,test'
        ];

        $request['status'] = $request->has('status') ? 1 : 0;

        $additionalData = [];

        if ($request['gateway'] == 'ssl_commerz') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'store_id' => 'required_if:status,1',
                'store_password' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paypal') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'client_id' => 'required_if:status,1',
                'client_secret' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'stripe') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'published_key' => 'required_if:status,1',
            ];
        } elseif ($request['gateway'] == 'razor_pay') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'api_key' => 'required_if:status,1',
                'api_secret' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'senang_pay') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_id' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paystack') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'public_key' => 'required_if:status,1',
                'secret_key' => 'required_if:status,1',
                'merchant_email' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'paymob_accept') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'callback_url' => 'required_if:status,1',
                'api_key' => 'required_if:status,1',
                'iframe_id' => 'required_if:status,1',
                'integration_id' => 'required_if:status,1',
                'hmac' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'mercadopago') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'access_token' => 'required_if:status,1',
                'public_key' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'flutterwave') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'secret_key' => 'required_if:status,1',
                'public_key' => 'required_if:status,1',
                'hash' => 'required_if:status,1'
            ];
        } elseif ($request['gateway'] == 'bkash') {
            $additionalData = [
                'status' => 'required|in:1,0',
                'app_key' => 'required_if:status,1',
                'app_secret' => 'required_if:status,1',
                'username' => 'required_if:status,1',
                'password' => 'required_if:status,1',
            ];
        }

        $request->validate(array_merge($validation, $additionalData));

        $settings = Setting::where('key_name', $request['gateway'])->where('settings_type', 'payment_config')->first();

        $additionalDataImage = $settings['additional_data'] != null ? json_decode($settings['additional_data']) : null;

        if ($request->has('gateway_image')) {
            $gatewayImage = Helpers::upload('payment_modules/gateway_image/', 'png', $request['gateway_image']);
        } else {
            $gatewayImage = $additionalDataImage != null ? $additionalDataImage->gateway_image : '';
        }

        if ($request['gateway_title'] == null) {
            Toastr::error(translate('payment_gateway_title_is_required!'));
            return back();
        }

        $paymentAdditionalData = [
            'gateway_title' => $request['gateway_title'],
            'gateway_image' => $gatewayImage,
        ];

        $validator = Validator::make($request->all(), array_merge($validation, $additionalData));

        Setting::updateOrCreate(['key_name' => $request['gateway'], 'settings_type' => 'payment_config'], [
            'key_name' => $request['gateway'],
            'live_values' => $validator->validate(),
            'test_values' => $validator->validate(),
            'settings_type' => 'payment_config',
            'mode' => $request['mode'],
            'is_active' => $request->status,
            'additional_data' => json_encode($paymentAdditionalData),
        ]);

        Toastr::success(GATEWAYS_DEFAULT_UPDATE_200['message']);
        return back();

    }

    /**
     * @return Renderable
     */
    public function termsAndConditions(): Renderable
    {
        $tnc = $this->business_setting->where(['key' => 'terms_and_conditions'])->first();
        if (!$tnc) {
            $this->business_setting->insert([
                'key' => 'terms_and_conditions',
                'value' => '',
            ]);
        }
        return view('admin-views.business-settings.terms-and-conditions', compact('tnc'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function termsAndConditionsUpdate(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'terms_and_conditions'])->update([
            'value' => $request->tnc,
        ]);

        Toastr::success(translate('Terms and Conditions updated!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function privacyPolicy(): Renderable
    {
        $data = $this->business_setting->where(['key' => 'privacy_policy'])->first();
        if (!$data) {
            $data = [
                'key' => 'privacy_policy',
                'value' => '',
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.privacy-policy', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function privacyPolicyUpdate(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'privacy_policy'])->update([
            'value' => $request->privacy_policy,
        ]);

        Toastr::success(translate('Privacy policy updated!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function aboutUs(): Renderable
    {
        $data = $this->business_setting->where(['key' => 'about_us'])->first();
        if (!$data) {
            $data = [
                'key' => 'about_us',
                'value' => '',
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.about-us', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function aboutUsUpdate(Request $request): RedirectResponse
    {
        $this->business_setting->where(['key' => 'about_us'])->update([
            'value' => $request->about_us,
        ]);

        Toastr::success(translate('About us updated!'));
        return back();
    }


    /**
     * @param Request $request
     * @return Renderable
     */
    public function returnPageIndex(Request $request): Renderable
    {
        $data = $this->business_setting->where(['key' => 'return_page'])->first();

        if (!$data) {
            $data = [
                'key' => 'return_page',
                'value' => json_encode([
                    'status' => 0,
                    'content' => ''
                ]),
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.return_page-index', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function returnPageUpdate(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'return_page'], [
            'value' => json_encode([
                'status' => $request['status'] == 1 ? 1 : 0,
                'content' => $request['content']
            ]),
        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }


    /**
     * @return Renderable
     */
    public function refundPageIndex(): Renderable
    {
        $data = $this->business_setting->where(['key' => 'refund_page'])->first();

        if (!$data) {
            $data = [
                'key' => 'refund_page',
                'value' => json_encode([
                    'status' => 0,
                    'content' => ''
                ]),
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.refund_page-index', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function refundPageUpdate(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'refund_page'], [
            'value' => json_encode([
                'status' => $request['status'] == 1 ? 1 : 0,
                'content' => $request['content'] == null ? null : $request['content']
            ]),
        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }


    /**
     * @return Renderable
     */
    public function cancellationPageIndex(): Renderable
    {
        $data = $this->business_setting->where(['key' => 'cancellation_page'])->first();

        if (!$data) {
            $data = [
                'key' => 'cancellation_page',
                'value' => json_encode([
                    'status' => 0,
                    'content' => ''
                ]),
            ];
            $this->business_setting->insert($data);
        }

        return view('admin-views.business-settings.cancellation_page-index', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancellationPageUpdate(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'cancellation_page'], [
            'value' => json_encode([
                'status' => $request['status'] == 1 ? 1 : 0,
                'content' => $request['content']
            ]),
        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function fcmIndex(): Renderable
    {
        $data = $this->business_setting->with('translations')->where(['key' => 'order_pending_message'])->first();
        if (!$this->business_setting->where(['key' => 'order_pending_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_pending_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_confirmation_msg'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_confirmation_msg',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_processing_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_processing_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'out_for_delivery_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'out_for_delivery_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'order_delivered_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'order_delivered_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'delivery_boy_assign_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'delivery_boy_assign_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'delivery_boy_start_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'delivery_boy_start_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'delivery_boy_delivered_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'delivery_boy_delivered_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'customer_notify_message'])->first()) {
            $this->business_setting->insert([
                'key' => 'customer_notify_message',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        if (!$this->business_setting->where(['key' => 'customer_notify_message_for_time_change'])->first()) {
            $this->business_setting->insert([
                'key' => 'customer_notify_message_for_time_change',
                'value' => json_encode([
                    'status' => 0,
                    'message' => '',
                ]),
            ]);
        }

        return view('admin-views.business-settings.fcm-index');
    }

    /**
     * @return Application|Factory|View
     */
    public function fcmConfig(): Factory|View|Application
    {
        if (!$this->business_setting->where(['key' => 'fcm_topic'])->first()) {
            $this->business_setting->insert([
                'key' => 'fcm_topic',
                'value' => '',
            ]);
        }
        if (!$this->business_setting->where(['key' => 'fcm_project_id'])->first()) {
            $this->business_setting->insert([
                'key' => 'fcm_project_id',
                'value' => '',
            ]);
        }
        if (!$this->business_setting->where(['key' => 'push_notification_key'])->first()) {
            $this->business_setting->insert([
                'key' => 'push_notification_key',
                'value' => '',
            ]);
        }

        $fcm_credentials = Helpers::get_business_settings('fcm_credentials');

        return view('admin-views.business-settings.fcm-config', compact('fcm_credentials'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateFcm(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'push_notification_service_file_content'], [
            'value' => $request['push_notification_service_file_content'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'fcm_project_id'], [
            'value' => $request['projectId'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'fcm_credentials'], [
            'value' => json_encode([
                'apiKey' => $request->apiKey,
                'authDomain' => $request->authDomain,
                'projectId' => $request->projectId,
                'storageBucket' => $request->storageBucket,
                'messagingSenderId' => $request->messagingSenderId,
                'appId' => $request->appId,
                'measurementId' => $request->measurementId
            ])
        ]);


        self::firebase_message_config_file_gen();

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return void
     */
    function firebase_message_config_file_gen(): void
    {
        $config = Helpers::get_business_settings('fcm_credentials');

        $apiKey = $config['apiKey'] ?? '';
        $authDomain = $config['authDomain'] ?? '';
        $projectId = $config['projectId'] ?? '';
        $storageBucket = $config['storageBucket'] ?? '';
        $messagingSenderId = $config['messagingSenderId'] ?? '';
        $appId = $config['appId'] ?? '';
        $measurementId = $config['measurementId'] ?? '';

        $filePath = base_path('firebase-messaging-sw.js');

        try {
            if (file_exists($filePath) && !is_writable($filePath)) {
                if (!chmod($filePath, 0644)) {
                    throw new \Exception('File is not writable and permission change failed: ' . $filePath);
                }
            }

            $fileContent = <<<JS
                importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
                importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

                firebase.initializeApp({
                    apiKey: "$apiKey",
                    authDomain: "$authDomain",
                    projectId: "$projectId",
                    storageBucket: "$storageBucket",
                    messagingSenderId: "$messagingSenderId",
                    appId: "$appId",
                    measurementId: "$measurementId"
                });

                const messaging = firebase.messaging();
                messaging.setBackgroundMessageHandler(function (payload) {
                    return self.registration.showNotification(payload.data.title, {
                        body: payload.data.body ? payload.data.body : '',
                        icon: payload.data.icon ? payload.data.icon : ''
                    });
                });
                JS;


            if (file_put_contents($filePath, $fileContent) === false) {
                throw new \Exception('Failed to write to file: ' . $filePath);
            }

        } catch (\Exception $e) {
            //
        }
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateFcmMessages(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'order_pending_message'], [
            'value' => json_encode([
                'status' => $request['pending_status'] == 1 ? 1 : 0,
                'message' => $request['pending_message'],
            ]),
        ]);
        $pendingOrder = $this->business_setting->where('key', 'order_pending_message')->first();

        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->order_pending_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $pendingOrder->id,
                        'locale' => $key,
                        'key' => 'order_pending_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'order_confirmation_msg'], [
            'value' => json_encode([
                'status' => $request['confirm_status'] == 1 ? 1 : 0,
                'message' => $request['confirm_message'],
            ]),
        ]);
        $confirmOrder = $this->business_setting->where('key', 'order_confirmation_msg')->first();

        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->order_confirmation_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $confirmOrder->id,
                        'locale' => $key,
                        'key' => 'order_confirmation_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'order_processing_message'], [
            'value' => json_encode([
                'status' => $request['processing_status'] == 1 ? 1 : 0,
                'message' => $request['processing_message'],
            ]),
        ]);
        $processingOrder = $this->business_setting->where('key', 'order_processing_message')->first();

        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->order_processing_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $processingOrder->id,
                        'locale' => $key,
                        'key' => 'order_processing_message'
                    ],
                    ['value' => $message]
                );
            }
        }


        $this->InsertOrUpdateBusinessData(['key' => 'out_for_delivery_message'], [
            'value' => json_encode([
                'status' => $request['out_for_delivery_status'] == 1 ? 1 : 0,
                'message' => $request['out_for_delivery_message'],
            ]),
        ]);
        $outForDelivery = $this->business_setting->where('key', 'out_for_delivery_message')->first();

        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->order_out_for_delivery_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $outForDelivery->id,
                        'locale' => $key,
                        'key' => 'order_out_for_delivery_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'order_delivered_message'], [
            'value' => json_encode([
                'status' => $request['delivered_status'] == 1 ? 1 : 0,
                'message' => $request['delivered_message'],
            ]),
        ]);
        $orderDelivered = $this->business_setting->where('key', 'order_delivered_message')->first();

        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->order_delivered_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $orderDelivered->id,
                        'locale' => $key,
                        'key' => 'order_delivered_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'delivery_boy_assign_message'], [
            'value' => json_encode([
                'status' => $request['delivery_boy_assign_status'] == 1 ? 1 : 0,
                'message' => $request['delivery_boy_assign_message'],
            ]),
        ]);
        $deliverymanAssign = $this->business_setting->where('key', 'delivery_boy_assign_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->assign_deliveryman_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $deliverymanAssign->id,
                        'locale' => $key,
                        'key' => 'assign_deliveryman_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'delivery_boy_start_message'], [
            'value' => json_encode([
                'status' => $request['delivery_boy_start_status'] == 1 ? 1 : 0,
                'message' => $request['delivery_boy_start_message'],
            ]),
        ]);
        $deliverymanStart = $this->business_setting->where('key', 'delivery_boy_start_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->deliveryman_start_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $deliverymanStart->id,
                        'locale' => $key,
                        'key' => 'deliveryman_start_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'delivery_boy_delivered_message'], [
            'value' => json_encode([
                'status' => $request['delivery_boy_delivered_status'] == 1 ? 1 : 0,
                'message' => $request['delivery_boy_delivered_message'],
            ]),
        ]);
        $deliverymanDelivered = $this->business_setting->where('key', 'delivery_boy_delivered_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->deliveryman_delivered_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $deliverymanDelivered->id,
                        'locale' => $key,
                        'key' => 'deliveryman_delivered_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'customer_notify_message'], [
            'value' => json_encode([
                'status' => $request['customer_notify_status'] == 1 ? 1 : 0,
                'message' => $request['customer_notify_message'],
            ]),
        ]);
        $customerNotify = $this->business_setting->where('key', 'customer_notify_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->customer_notification_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $customerNotify->id,
                        'locale' => $key,
                        'key' => 'customer_notification_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'customer_notify_message_for_time_change'], [
            'value' => json_encode([
                'status' => $request['customer_notify_status_for_time_change'] == 1 ? 1 : 0,
                'message' => $request['customer_notify_message_for_time_change'],
            ]),
        ]);
        $notifyForTimeChange = $this->business_setting->where('key', 'customer_notify_message_for_time_change')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->notify_for_time_change_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $notifyForTimeChange->id,
                        'locale' => $key,
                        'key' => 'notify_for_time_change_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'returned_message'], [
            'value' => json_encode([
                'status' => $request['returned_status'] == 1 ? 1 : 0,
                'message' => $request['returned_message'],
            ]),
        ]);
        $returnOrder = $this->business_setting->where('key', 'returned_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->return_order_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $returnOrder->id,
                        'locale' => $key,
                        'key' => 'return_order_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'failed_message'], [
            'value' => json_encode([
                'status' => $request['failed_status'] == 1 ? 1 : 0,
                'message' => $request['failed_message'],
            ]),
        ]);
        $failedOrder = $this->business_setting->where('key', 'failed_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->failed_order_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $failedOrder->id,
                        'locale' => $key,
                        'key' => 'failed_order_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'canceled_message'], [
            'value' => json_encode([
                'status' => $request['canceled_status'] == 1 ? 1 : 0,
                'message' => $request['canceled_message'],
            ]),
        ]);
        $canceledOrder = $this->business_setting->where('key', 'canceled_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->canceled_order_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $canceledOrder->id,
                        'locale' => $key,
                        'key' => 'canceled_order_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'add_wallet_message'], [
            'value' => json_encode([
                'status' => $request['add_wallet_status'] == 1 ? 1 : 0,
                'message' => $request['add_wallet_message'],
            ]),
        ]);
        $addWallet = $this->business_setting->where('key', 'add_wallet_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->add_fund_wallet_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $addWallet->id,
                        'locale' => $key,
                        'key' => 'add_fund_wallet_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'add_wallet_bonus_message'], [
            'value' => json_encode([
                'status' => $request['add_wallet_bonus_status'] == 1 ? 1 : 0,
                'message' => $request['add_wallet_bonus_message'],
            ]),
        ]);
        $addWalletBonus = $this->business_setting->where('key', 'add_wallet_bonus_message')->first();
        foreach ($request->lang as $index => $key) {
            if ($key === 'default') {
                continue;
            }
            $message = $request->add_fund_wallet_bonus_message[$index - 1] ?? null;
            if ($message !== null) {
                Translation::updateOrInsert(
                    [
                        'translationable_type' => 'App\Model\BusinessSetting',
                        'translationable_id' => $addWalletBonus->id,
                        'locale' => $key,
                        'key' => 'add_fund_wallet_bonus_message'
                    ],
                    ['value' => $message]
                );
            }
        }

        Toastr::success(translate('Message updated!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function mapApiSettings(): Renderable
    {
        return view('admin-views.business-settings.map-api');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateMapApi(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'map_api_server_key'], [
            'value' => $request['map_api_server_key'],
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'map_api_client_key'], [
            'value' => $request['map_api_client_key'],
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return Renderable
     */
    public function recaptchaIndex(Request $request): Renderable
    {
        return view('admin-views.business-settings.recaptcha-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function recaptchaUpdate(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'recaptcha'], [
            'value' => json_encode([
                'status' => $request['status'],
                'site_key' => $request['site_key'],
                'secret_key' => $request['secret_key']
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Toastr::success(translate('Updated Successfully'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function appSettingIndex(): Renderable
    {
        return view('admin-views.business-settings.app-setting-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function appSettingUpdate(Request $request): RedirectResponse
    {
        if ($request->platform == 'android') {
            $this->InsertOrUpdateBusinessData(['key' => 'play_store_config'], [
                'value' => json_encode([
                    'status' => $request['play_store_status'],
                    'link' => $request['play_store_link'],
                    'min_version' => $request['android_min_version'],

                ]),
            ]);

            Toastr::success(translate('Updated Successfully for Android'));
            return back();
        }

        if ($request->platform == 'ios') {
            $this->InsertOrUpdateBusinessData(['key' => 'app_store_config'], [
                'value' => json_encode([
                    'status' => $request['app_store_status'],
                    'link' => $request['app_store_link'],
                    'min_version' => $request['ios_min_version'],
                ]),
            ]);
            Toastr::success(translate('Updated Successfully for IOS'));
            return back();
        }

        Toastr::error(translate('Updated failed'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function firebaseMessageConfigIndex(): Renderable
    {
        return view('admin-views.business-settings.firebase-config-index');
    }

    /**
     * @return Renderable
     */
    public function socialMedia(): Renderable
    {
        return view('admin-views.business-settings.social-media');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $data = $this->social_media->orderBy('id', 'desc')->get();
            return response()->json($data);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaStore(Request $request): JsonResponse
    {
        try {
            $this->social_media->updateOrInsert([
                'name' => $request->get('name'),
            ], [
                'name' => $request->get('name'),
                'link' => $request->get('link'),
            ]);

            return response()->json([
                'success' => 1,
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'error' => 1,
            ]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaEdit(Request $request): JsonResponse
    {
        $data = $this->social_media->where('id', $request->id)->first();
        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaUpdate(Request $request): JsonResponse
    {
        $socialMedia = $this->social_media->find($request->id);
        $socialMedia->name = $request->name;
        $socialMedia->link = $request->link;
        $socialMedia->save();

        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaDelete(Request $request): JsonResponse
    {
        $br = $this->social_media->find($request->id);
        $br->delete();
        return response()->json();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function socialMediaStatusUpdate(Request $request): JsonResponse
    {
        $this->social_media->where(['id' => $request['id']])->update([
            'status' => $request['status'],
        ]);
        return response()->json([
            'success' => 1,
        ], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateDeliveryFee(Request $request): RedirectResponse
    {
        if ($request->delivery_charge == null) {
            $request->delivery_charge = $this->business_setting->where(['key' => 'delivery_charge'])->first()->value;
        }
        $this->InsertOrUpdateBusinessData(['key' => 'delivery_charge'], [
            'value' => $request->delivery_charge,
        ]);

        if ($request['min_shipping_charge'] == null) {
            $request['min_shipping_charge'] = Helpers::get_business_settings('delivery_management')['min_shipping_charge'];
        }
        if ($request['shipping_per_km'] == null) {
            $request['shipping_per_km'] = Helpers::get_business_settings('delivery_management')['shipping_per_km'];
        }
        if ($request['shipping_status'] == 1) {
            $request->validate([
                'min_shipping_charge' => 'required',
                'shipping_per_km' => 'required',
            ],
                [
                    'min_shipping_charge.required' => 'Minimum shipping charge is required while shipping method is active',
                    'shipping_per_km.required' => 'Shipping charge per Kilometer is required while shipping method is active',
                ]);
        }

        $this->InsertOrUpdateBusinessData(['key' => 'delivery_management'], [
            'value' => json_encode([
                'status' => $request['shipping_status'],
                'min_shipping_charge' => $request['min_shipping_charge'],
                'shipping_per_km' => $request['shipping_per_km'],
            ]),
        ]);

        Toastr::success(translate('Delivery_fee_updated_successfully'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function mainBranchSetup(): Renderable
    {
        $branch = $this->branch->find(1);
        return view('admin-views.business-settings.restaurant.main-branch', compact('branch'));
    }

    /**
     * @return Renderable
     */
    public function socialLogin(): Renderable
    {
        $apple = BusinessSetting::where('key', 'apple_login')->first();
        if (!$apple) {
            $this->InsertOrUpdateBusinessData(['key' => 'apple_login'], [
                'value' => '{"login_medium":"apple","client_id":"","client_secret":"","team_id":"","key_id":"","service_file":"","redirect_url":"","status":""}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $apple = BusinessSetting::where('key', 'apple_login')->first();
        }
        $appleLoginService = json_decode($apple->value, true);
        return view('admin-views.business-settings.social-login', compact('appleLoginService'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateAppleLogin(Request $request): RedirectResponse
    {
        $apple_login = Helpers::get_business_settings('apple_login');

        if ($request->hasFile('service_file')) {
            $fileName = Helpers::upload('apple-login/', 'p8', $request->file('service_file'));
        }

        $data = [
            'value' => json_encode([
                'login_medium' => 'apple',
                'client_id' => $request['client_id'],
                'client_secret' => '',
                'team_id' => $request['team_id'],
                'key_id' => $request['key_id'],
                'service_file' => $fileName ?? $apple_login['service_file'],
                'redirect_url' => '',
            ]),
        ];

        $this->InsertOrUpdateBusinessData(['key' => 'apple_login'], $data);

        Toastr::success(translate('settings updated!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function chatIndex(): Renderable
    {
        return view('admin-views.business-settings.chat-index');
    }

    /**
     * @param Request $request
     * @param $name
     * @return RedirectResponse
     */
    public function chatUpdate(Request $request, $name): RedirectResponse
    {
        if ($name == 'whatsapp') {
            $this->InsertOrUpdateBusinessData(['key' => 'whatsapp'], [
                'value' => json_encode([
                    'status' => $request['status'] == 'on' ? 1 : 0,
                    'number' => $request['number'],
                ]),
            ]);
        }

        Toastr::success(translate('chat settings updated!'));
        return back();
    }

    /**
     * @return Renderable
     */
    public function cookiesSetup(): Renderable
    {
        return view('admin-views.business-settings.cookies-setup-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function cookiesSetupUpdate(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'cookies'], [
            'value' => json_encode([
                'status' => $request['status'],
                'text' => $request['text'],
            ])
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    public function OTPSetup(): Factory|View|Application
    {
        return view('admin-views.business-settings.otp-setup');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function OTPSetupUpdate(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'maximum_otp_hit'], [
            'value' => $request['maximum_otp_hit'],
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'otp_resend_time'], [
            'value' => $request['otp_resend_time'],
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'temporary_block_time'], [
            'value' => $request['temporary_block_time'],
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'maximum_login_hit'], [
            'value' => $request['maximum_login_hit'],
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'temporary_login_block_time'], [
            'value' => $request['temporary_login_block_time'],
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function orderIndex(): Factory|View|Application
    {
        return view('admin-views.business-settings.order-index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function orderUpdate(Request $request): RedirectResponse
    {
        $request['cutlery_status'] = $request->has('cutlery_status') ? 1 : 0;

        $this->InsertOrUpdateBusinessData(['key' => 'minimum_order_value'], [
            'value' => $request['minimum_order_value'],
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'default_preparation_time'], [
            'value' => $request['default_preparation_time'],
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'schedule_order_slot_duration'], [
            'value' => $request['schedule_order_slot_duration']
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'cutlery_status'], [
            'value' => $request['cutlery_status']
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function customerSettings(): Factory|View|Application
    {
        $data = $this->business_setting->where('key', 'like', 'wallet_%')
            ->orWhere('key', 'like', 'loyalty_%')
            ->orWhere('key', 'like', 'ref_earning_%')
            ->orWhere('key', 'like', 'ref_earning_%')
            ->orWhere('key', 'like', 'add_fund_to_wallet')
            ->get();
        $data = array_column($data->toArray(), 'value', 'key');

        return view('admin-views.business-settings.customer-settings', compact('data'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function customerSettingsUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'loyalty_point_item_purchase_point' => 'nullable|numeric',
            'loyalty_point_exchange_rate' => 'nullable|numeric',
            'ref_earning_exchange_rate' => 'nullable|numeric',
            'loyalty_point_minimum_point' => 'nullable|numeric',
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'wallet_status'], [
            'value' => $request['customer_wallet'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'loyalty_point_status'], [
            'value' => $request['customer_loyalty_point'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'ref_earning_status'], [
            'value' => $request['ref_earning_status'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'add_fund_to_wallet'], [
            'value' => $request['add_fund_to_wallet'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'loyalty_point_exchange_rate'], [
            'value' => $request['loyalty_point_exchange_rate'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'ref_earning_exchange_rate'], [
            'value' => $request['ref_earning_exchange_rate'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'loyalty_point_item_purchase_point'], [
            'value' => $request['item_purchase_point'] ?? 0
        ]);
        $this->InsertOrUpdateBusinessData(['key' => 'loyalty_point_minimum_point'], [
            'value' => $request['minimun_transfer_point'] ?? 0
        ]);

        Toastr::success(translate('customer_settings_updated_successfully'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function firebaseOTPVerification(): Factory|View|Application
    {
        return view('admin-views.business-settings.firebase-otp-verification');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function firebaseOTPVerificationUpdate(Request $request): RedirectResponse
    {
        if ($request->has('status')) {
            $request->validate([
                'web_api_key' => 'required',
            ]);
        }

        $this->InsertOrUpdateBusinessData(['key' => 'firebase_otp_verification'], [
            'value' => json_encode([
                'status' => $request->has('status') ? 1 : 0,
                'web_api_key' => $request['web_api_key'],
            ]),
        ]);

        if ($request->has('status')) {
            foreach (['twilio', 'nexmo', '2factor', 'msg91', 'signal_wire'] as $gateway) {
                $keep = Setting::where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->first();
                if (isset($keep)) {
                    $hold = $keep->live_values;
                    $hold['status'] = 0;
                    Setting::where(['key_name' => $gateway, 'settings_type' => 'sms_config'])->update([
                        'live_values' => $hold,
                        'test_values' => $hold,
                        'is_active' => 0,
                    ]);
                }
            }
        }

        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function productIndex(): Factory|View|Application
    {
        $searchPlaceholder = $this->business_setting->where('key', 'search_placeholder')->first();
        return view('admin-views.business-settings.product-index', compact('searchPlaceholder'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function searchPlaceholderStore(Request $request): RedirectResponse
    {
        $request->validate([
            'placeholder_name' => 'required',
        ]);

        $searchPlaceholder = $this->business_setting->where('key', 'search_placeholder')->first();
        if ($searchPlaceholder) {
            $data = json_decode($searchPlaceholder->value, true);
        } else {
            $data = [];
        }

        $id = $request->input('id');
        $existingSearchPlaceholder = null;
        foreach ($data as $key => $item) {
            if ($item['id'] == (int)$id) {
                $existingSearchPlaceholder = $key;
                break;
            }
        }

        if ($existingSearchPlaceholder !== null) {
            $data[$existingSearchPlaceholder]['id'] = (int)$id;
            $data[$existingSearchPlaceholder]['placeholder_name'] = $request['placeholder_name'];
        } else {
            $newItem = [
                'id' => rand(1000000000, 9999999999),
                'placeholder_name' => $request['placeholder_name'],
                'status' => "1",
            ];

            $data[] = $newItem;
        }

        $this->business_setting->query()->updateOrInsert(['key' => 'search_placeholder'], [
            'value' => json_encode($data)
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function searchPlaceholderStatus($id): RedirectResponse
    {
        $searchPlaceholder = $this->business_setting->where('key', 'search_placeholder')->first();
        if ($searchPlaceholder) {
            $data = json_decode($searchPlaceholder->value, true);
        } else {
            $data = [];
        }
        foreach ($data as $value) {
            if ($value['id'] == $id) {
                $value['status'] = ($value['status'] == 0) ? 1 : 0;
            }
            $array[] = $value;
        }

        $this->business_setting->query()->updateOrInsert(['key' => 'search_placeholder'], [
            'value' => $array
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param $id
     * @return RedirectResponse
     */
    public function searchPlaceholderDelete($id): RedirectResponse
    {
        $searchPlaceholder = $this->business_setting->where('key', 'search_placeholder')->first();
        if ($searchPlaceholder) {
            $data = json_decode($searchPlaceholder->value, true);
        } else {
            $data = [];
        }
        foreach ($data as $value) {
            if ($value['id'] != $id) {
                $array[] = $value;
            }
        }
        $this->business_setting->query()->updateOrInsert(['key' => 'search_placeholder'], [
            'value' => $array
        ]);

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    public function maintenanceModeSetup(Request $request): RedirectResponse
    {
        $this->InsertOrUpdateBusinessData(['key' => 'maintenance_mode'], [
            'value' => $request->has('maintenance_mode') ? 1 : 0
        ]);

        $selectedSystems = [];
        $systems = ['branch_panel', 'customer_app', 'web_app', 'deliveryman_app', 'table_app', 'kitchen_app'];

        foreach ($systems as $system) {
            if ($request->has($system)) {
                $selectedSystems[] = $system;
            }
        }

        $this->InsertOrUpdateBusinessData(['key' => 'maintenance_system_setup'], [
            'value' => json_encode($selectedSystems)],
        );

        $this->InsertOrUpdateBusinessData(['key' => 'maintenance_duration_setup'], [
            'value' => json_encode([
                'maintenance_duration' => $request['maintenance_duration'],
                'start_date' => $request['start_date'] ?? null,
                'end_date' => $request['end_date'] ?? null,
            ]),
        ]);

        $this->InsertOrUpdateBusinessData(['key' => 'maintenance_message_setup'], [
            'value' => json_encode([
                'business_number' => $request->has('business_number') ? 1 : 0,
                'business_email' => $request->has('business_email') ? 1 : 0,
                'maintenance_message' => $request['maintenance_message'],
                'message_body' => $request['message_body']
            ]),
        ]);

        $maintenanceStatus = (integer)(Helpers::get_business_settings('maintenance_mode') ?? 0);
        $selectedMaintenanceDuration = Helpers::get_business_settings('maintenance_duration_setup') ?? [];
        $selectedMaintenanceSystem = Helpers::get_business_settings('maintenance_system_setup') ?? [];
        $isBranch = in_array('branch_panel', $selectedMaintenanceSystem) ? 1 : 0;

        $maintenance = [
            'status' => $maintenanceStatus,
            'start_date' => $request->input('start_date', null),
            'end_date' => $request->input('end_date', null),
            'branch_panel' => $isBranch,
            'maintenance_duration' => $selectedMaintenanceDuration['maintenance_duration'],
            'maintenance_messages' => Helpers::get_business_settings('maintenance_message_setup') ?? [],
        ];


        Cache::put('maintenance', $maintenance, now()->addYears(1));

        $this->sendMaintenanceModeNotification();

        Toastr::success(translate('Settings updated!'));
        return back();

    }

    private function sendMaintenanceModeNotification(): void
    {
        $data = [
            'title' => translate('Maintenance Mode Settings Updated'),
            'description' => translate('Maintenance Mode Settings Updated'),
            'order_id' => '',
            'image' => ''
        ];

        try {
            Helpers::send_push_notif_to_topic($data, 'notify', 'maintenance');
            Helpers::send_push_notif_to_topic($data, "deliveryman", 'maintenance');
        } catch (\Exception $e) {
        }
    }

    public function marketingTools()
    {
        return view('admin-views.business-settings.marketing-tools');
    }

    public function updateMarketingTools(Request $request, $type)
    {

        if ($type == 'meta') {
            $this->InsertOrUpdateBusinessData(['key' => 'meta_pixel'], [
                'value' => json_encode([
                    'status' => $request->has('status') ? 1 : 0,
                    'meta_app_id' => $request['meta_app_id'],
                ]),
            ]);
        }

        Toastr::success(translate('Successfully updated!'));
        return back();
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    private function InsertOrUpdateBusinessData($key, $value): void
    {
        $businessSetting = $this->business_setting->where(['key' => $key['key']])->first();
        if ($businessSetting) {
            $businessSetting->value = $value['value'];
            $businessSetting->save();
        } else {
            $this->business_setting->create($key, $value);
        }
    }
}
