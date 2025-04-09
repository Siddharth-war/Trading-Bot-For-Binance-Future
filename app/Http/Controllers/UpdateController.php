<?php

namespace App\Http\Controllers;

use App\CentralLogics\Helpers;
use App\Model\AddOn;
use App\Model\Admin;
use App\Model\AdminRole;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Models\LoginSetup;
use App\Traits\ActivationClass;
use App\User;
use App\Models\DeliveryChargeSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Brian2694\Toastr\Facades\Toastr;


class UpdateController extends Controller
{
    use ActivationClass;
    public function update_software_index()
    {
        return view('update.update-software');
    }

    public function update_software(Request $request)
    {
        Helpers::setEnvironmentValue('SOFTWARE_ID', 'MzAzMjAzMzg=');
        Helpers::setEnvironmentValue('BUYER_USERNAME', $request['username']);
        Helpers::setEnvironmentValue('PURCHASE_CODE', $request['purchase_key']);
        Helpers::setEnvironmentValue('APP_MODE', 'live');
        Helpers::setEnvironmentValue('SOFTWARE_VERSION', '11.2');
        Helpers::setEnvironmentValue('APP_NAME', 'efood');

        $data = $this->actch();
        try {
            if (!$data->getData()->active) {
                $remove = array("http://", "https://", "www.");
                $url = str_replace($remove, "", url('/'));
                $activation_url = base64_decode('aHR0cHM6Ly9hY3RpdmF0aW9uLjZhbXRlY2guY29t');
                $activation_url .= '?username=' . $request['username'];
                $activation_url .= '&purchase_code=' . $request['purchase_key'];
                $activation_url .= '&domain=' . $url . '&';
                return redirect($activation_url);
            }
        } catch (\Exception $exception) {
            Toastr::error('verification failed! try again');
            return back();
        }

        Artisan::call('migrate', ['--force' => true]);

        $previousRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvier = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvier, $previousRouteServiceProvier);

        Artisan::call('optimize:clear');


        DB::table('business_settings')->updateOrInsert(['key' => 'self_pickup'], [
            'value' => 1
        ]);

        DB::table('business_settings')->updateOrInsert(['key' => 'delivery'], [
            'value' => 1
        ]);

        if (BusinessSetting::where(['key' => 'paystack'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'paystack',
                'value' => '{"status":"1","publicKey":"","razor_secret":"","secretKey":"","paymentUrl":"","merchantEmail":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'senang_pay'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'senang_pay',
                'value' => '{"status":"1","secret_key":"","merchant_id":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'bkash'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'bkash',
                'value' => '{"status":"1","api_key":"","api_secret":"","username":"","password":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'paymob'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'paymob',
                'value' => '{"status":"1","api_key":"","iframe_id":"","integration_id":"","hmac":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'flutterwave'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'flutterwave',
                'value' => '{"status":"1","public_key":"","secret_key":"","hash":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'mercadopago'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'mercadopago',
                'value' => '{"status":"1","public_key":"","access_token":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'paypal'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'paypal',
                'value' => '{"status":"1","paypal_client_id":"","paypal_secret":""}'
            ]);
        }
        if (BusinessSetting::where(['key' => 'internal_point'])->first() == false) {
            BusinessSetting::insert([
                'key' => 'internal_point',
                'value' => '{"status":"1"}'
            ]);
        }
        Order::where('delivery_date', null)->update([
            'delivery_date' => date('y-m-d', strtotime("-1 days")),
            'delivery_time' => '12:00',
            'updated_at' => now()
        ]);

        if (BusinessSetting::where(['key' => 'language'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'language'], [
                'value' => json_encode(["en"])
            ]);
        }
        if (BusinessSetting::where(['key' => 'time_zone'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'time_zone'], [
                'value' => 'Pacific/Midway'
            ]);
        }

        DB::table('business_settings')->updateOrInsert(['key' => 'phone_verification'], [
            'value' => 0
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'msg91_sms'], [
            'key' => 'msg91_sms',
            'value' => '{"status":0,"template_id":null,"authkey":null}'
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => '2factor_sms'], [
            'key' => '2factor_sms',
            'value' => '{"status":"0","api_key":null}'
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'nexmo_sms'], [
            'key' => 'nexmo_sms',
            'value' => '{"status":0,"api_key":null,"api_secret":null,"signature_secret":"","private_key":"","application_id":"","from":null,"otp_template":null}'
        ]);
        DB::table('business_settings')->updateOrInsert(['key' => 'twilio_sms'], [
            'key' => 'twilio_sms',
            'value' => '{"status":0,"sid":null,"token":null,"from":null,"otp_template":null}'
        ]);
        if (BusinessSetting::where(['key' => 'pagination_limit'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'pagination_limit'], [
                'value' => 10
            ]);
        }
        if (BusinessSetting::where(['key' => 'default_preparation_time'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'default_preparation_time'], [
                'value' => 30
            ]);
        }
        if(BusinessSetting::where(['key' => 'decimal_point_settings'])->first() == false)
        {
            DB::table('business_settings')->updateOrInsert(['key' => 'decimal_point_settings'], [
                'value' => 2
            ]);
        }
        if (BusinessSetting::where(['key' => 'map_api_key'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'map_api_key'], [
                'value' => ''
            ]);
        }

        if (BusinessSetting::where(['key' => 'play_store_config'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'play_store_config'], [
                'value' => '{"status":"","link":"","min_version":""}'
            ]);
        } else {
            $play_store_config = Helpers::get_business_settings('play_store_config');
            DB::table('business_settings')->updateOrInsert(['key' => 'play_store_config'], [
                'value' => json_encode([
                    'status' => $play_store_config['status'],
                    'link' => $play_store_config['link'],
                    'min_version' => "1",
                ])
            ]);
        }

        if (BusinessSetting::where(['key' => 'app_store_config'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'app_store_config'], [
                'value' => '{"status":"","link":"","min_version":""}'
            ]);
        } else {
            $app_store_config = Helpers::get_business_settings('app_store_config');
            DB::table('business_settings')->updateOrInsert(['key' => 'app_store_config'], [
                'value' => json_encode([
                    'status' => $app_store_config['status'],
                    'link' => $app_store_config['link'],
                    'min_version' => "1",
                ])
            ]);
        }

        if (BusinessSetting::where(['key' => 'delivery_management'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'delivery_management'], [
                'value' => json_encode([
                    'status' => 0,
                    'min_shipping_charge' => 0,
                    'shipping_per_km' => 0,
                ]),
            ]);
        }
        if (BusinessSetting::where(['key' => 'recaptcha'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'recaptcha'], [
                'value' => '{"status":"0","site_key":"","secret_key":""}'
            ]);
        }


        //for modified language [new multi lang in admin]
        $languages = Helpers::get_business_settings('language');
        $lang_array = [];
        $lang_flag = false;

        foreach ($languages as $key => $language) {
            if(gettype($language) != 'array') {
                $lang = [
                    'id' => $key+1,
                    'name' => $language,
                    'direction' => 'ltr',
                    'code' => $language,
                    'status' => 1,
                    'default' => $language == 'en' ? true : false,
                ];

                array_push($lang_array, $lang);
                $lang_flag = true;
            }
        }
        if ($lang_flag == true) {
            BusinessSetting::where('key', 'language')->update([
                'value' => $lang_array
            ]);
        }
        //lang end

        if (BusinessSetting::where(['key' => 'schedule_order_slot_duration'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'schedule_order_slot_duration'], [
                'value' => '1'
            ]);
        }

        if (BusinessSetting::where(['key' => 'time_format'])->first() == false) {
            DB::table('business_settings')->updateOrInsert(['key' => 'time_format'], [
                'value' => '24'
            ]);
        }

        //for role management
        $admin_role = AdminRole::get()->first();
        if (!$admin_role) {
            DB::table('admin_roles')->insertOrIgnore([
                'id' => 1,
                'name' => 'Master Admin',
                'module_access' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $admin = Admin::get()->first();
        if($admin) {
            $admin->admin_role_id = 1;
            $admin->save();
        }

        $mail_config = \App\CentralLogics\Helpers::get_business_settings('mail_config');
        BusinessSetting::where(['key' => 'mail_config'])->update([
            'value' => json_encode([
                "status" => 0,
                "name" => $mail_config['name']??'',
                "host" => $mail_config['host']??'',
                "driver" => $mail_config['driver']??'',
                "port" => $mail_config['port']??'',
                "username" => $mail_config['username']??'',
                "email_id" => $mail_config['email_id']??'',
                "encryption" => $mail_config['encryption']??'',
                "password" => $mail_config['password']??''
            ]),
        ]);

        //*** auto run script ***
        try {
            $order_details = OrderDetail::get();
            foreach($order_details as $order_detail) {

                //*** addon quantity integer casting script ***
                $qtys = json_decode($order_detail['add_on_qtys'], true);
                array_walk($qtys, function (&$add_on_qtys) {
                    $add_on_qtys = (int) $add_on_qtys;
                });
                $order_detail['add_on_qtys'] = json_encode($qtys);
                //*** end ***


                //*** variation(POS) structure change script ***
                $variation = json_decode($order_detail['variation'], true);
                $product = json_decode($order_detail['product_details'], true);

                if(count($variation) > 0) {
                    $result = [];
                    if(!array_key_exists('price', $variation[0])) {
                        $result[] = [
                            'type' => $variation[0]['Size'],
                            'price' => Helpers::set_price($product['price'])
                        ];
                    }
                    if(count($result) > 0) {
                        $order_detail['variation'] = json_encode($result);
                    }

                }
                //*** end ***

                $order_detail->save();


            }
        } catch (\Exception $exception) {
            //
        }
        //*** end ***

        DB::table('branches')->insertOrIgnore([
            'id' => 1,
            'name' => 'Main Branch',
            'email' => 'main@gmail.com',
            'password' => '',
            'coverage' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        if (!BusinessSetting::where(['key' => 'wallet_status'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'wallet_status'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'loyalty_point_status'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'loyalty_point_status'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'ref_earning_status'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'ref_earning_status'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'loyalty_point_exchange_rate'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'loyalty_point_exchange_rate'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'ref_earning_exchange_rate'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'ref_earning_exchange_rate'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'loyalty_point_item_purchase_point'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'loyalty_point_item_purchase_point'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'loyalty_point_minimum_point'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'loyalty_point_minimum_point'], [
                'value' => '0'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'whatsapp'])->first()) {
            BusinessSetting::insert([
                'key' => 'whatsapp',
                'value' => '{"status":0,"number":""}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'fav_icon'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'fav_icon'], [
                'value' => ''
            ]);
        }

        //user referral code
        $users = User::whereNull('refer_code')->get();
        foreach ($users as $user) {
            $user->refer_code = Helpers::generate_referer_code();
            $user->save();
        }

        if (!BusinessSetting::where(['key' => 'cookies'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'cookies'], [
                'value' => '{"status":"1","text":"Allow Cookies for this site"}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'maximum_otp_hit'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'maximum_otp_hit'], [
                'value' => 5
            ]);
        }

        if (!BusinessSetting::where(['key' => 'otp_resend_time'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'otp_resend_time'], [
                'value' => 60
            ]);
        }

        if (!BusinessSetting::where(['key' => 'temporary_block_time'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'temporary_block_time'], [
                'value' => 600
            ]);
        }

        if (!BusinessSetting::where(['key' => 'dm_self_registration'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'dm_self_registration'], [
                'value' => 1
            ]);
        }

        if (!BusinessSetting::where(['key' => 'toggle_veg_non_veg'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'toggle_veg_non_veg'], [
                'value' => 1
            ]);
        }

        if (!BusinessSetting::where(['key' => 'maximum_login_hit'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'maximum_login_hit'], [
                'value' => 5
            ]);
        }

        if (!BusinessSetting::where(['key' => 'temporary_login_block_time'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'temporary_login_block_time'], [
                'value' => 600
            ]);
        }

        /* update old order details for addon*/
        $or_details = OrderDetail::with('order')->get();

        foreach ($or_details as $detail){
            $add_on_taxes = [];
            $add_on_prices = [];
            $add_on_tax_amount = 0;

            // Check if add-on ids and quantities are set and non-empty
            if (isset($detail['add_on_ids']) && count(json_decode($detail['add_on_ids'],true)) >0 && isset($detail['add_on_qtys']) && count(json_decode($detail['add_on_qtys'],true)) >0){
                if ($detail->order->order_type == 'pos'){
                    $product_details = json_decode($detail->product_details, true);
                    $add_on_ids = json_decode($detail['add_on_ids'], true);

                    foreach($product_details['add_ons'] as $add_on){
                        if (in_array($add_on['id'], $add_on_ids)) {
                            $add_on_prices[] = $add_on['price'];
                            $add_on_taxes[] = 0;
                        }
                    }
                }else{
                    foreach(json_decode($detail['add_on_ids'], true) as $id){
                        $addon = AddOn::find($id);

                        if ($addon) {
                            $add_on_prices[] = $addon['price'];
                        }else{
                            $add_on_prices[] = 0;
                        }
                        $add_on_taxes[] = 0;
                        $add_on_tax_amount = 0;
                    }
                }
            }else{
                $add_on_taxes = [];
                $add_on_prices = [];
                $add_on_tax_amount = 0;
            }

            // Update the order_details table with the new values
            $detail->add_on_taxes = $add_on_taxes;
            $detail->add_on_prices = $add_on_prices;
            $detail->add_on_tax_amount = $add_on_tax_amount;
            $detail->save();
        }

        if (!BusinessSetting::where(['key' => 'return_page'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'return_page'], [
                'value' => '{"status":"0","content":""}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'refund_page'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'refund_page'], [
                'value' => '{"status":"0","content":""}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'cancellation_page'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'cancellation_page'], [
                'value' => '{"status":"0","content":""}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'offline_payment'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'offline_payment'], [
                'value' => '{"status":"1"}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'guest_checkout'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'guest_checkout'], [
                'value' => 1
            ]);
        }

        if (!BusinessSetting::where(['key' => 'partial_payment'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'partial_payment'], [
                'value' => 1
            ]);
        }

        if (!BusinessSetting::where(['key' => 'partial_payment_combine_with'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'partial_payment_combine_with'], [
                'value' => 'all'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'qr_code'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'qr_code'], [
                'value' => '{"branch_id":"1","logo":"","title":"","description":"","opening_time":"","closing_time":"","phone":"","website":"","social_media":""}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'apple_login'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'apple_login'], [
                'value' => '{"login_medium":"apple","client_id":"","client_secret":"","team_id":"","key_id":"","service_file":"","redirect_url":"","status":0}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'add_wallet_message'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'add_wallet_message'], [
                'value' => '{"status":0,"message":""}'
            ]);
        }

        if (!BusinessSetting::where(['key' => 'add_wallet_bonus_message'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'add_wallet_bonus_message'], [
                'value' => '{"status":0,"message":""}'
            ]);
        }

        //new database table
        if (!Schema::hasTable('addon_settings')) {
            $sql = File::get(base_path($request['path'] . 'database/addon_settings.sql'));
            DB::unprepared($sql);
            $this->set_payment_data();

        }
        $this->set_sms_data();


        if (!Schema::hasTable('payment_requests')) {
            $sql = File::get(base_path($request['path'] . 'database/payment_requests.sql'));
            DB::unprepared($sql);
        }

        DB::table('business_settings')->updateOrInsert(['key' => 'firebase_otp_verification'], [
            'value' => json_encode([
                'status'  => 0,
                'web_api_key' => '',
            ]),
        ]);

        //version_11.0
        $default_preparation_time = Helpers::get_business_settings('default_preparation_time');
        $branches = Branch::where(['preparation_time' => null])->get();
        foreach ($branches as $branch) {
            $branch->preparation_time = $default_preparation_time;
            $branch->save();
        }

        if (!BusinessSetting::where(['key' => 'footer_description_text'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'footer_description_text'], [
                'value' => ''
            ]);
        }

        if (!BusinessSetting::where(['key' => 'push_notification_service_file_content'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'push_notification_service_file_content'], [
                'value' => ''
            ]);
        }

        //version_11.1
        if (!BusinessSetting::where(['key' => 'maintenance_system_setup'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'maintenance_system_setup'], [
                'value' => json_encode([])
            ]);
        }
        if (!BusinessSetting::where(['key' => 'maintenance_duration_setup'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'maintenance_duration_setup'], [
                'value' => json_encode([
                    'maintenance_duration'  => "until_change",
                    'start_date'  => null,
                    'end_date'  => null,
                ]),
            ]);
        }
        if (!BusinessSetting::where(['key' => 'maintenance_message_setup'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'maintenance_message_setup'], [
                'value' => json_encode([
                    'business_number'  => 1,
                    'business_email'  => 1,
                    'maintenance_message'  => "We are Cooking Up Something Special!",
                    'message_body'  => "Our system is currently undergoing maintenance to bring you an even tastier experience. Hang tight while we make the dishes.",
                ]),
            ]);
        }

        $emailVerification = (integer)Helpers::get_business_settings('email_verification') ?? 0;
        $phoneVerification = (integer)Helpers::get_business_settings('phone_verification') ?? 0;

        if (!LoginSetup::where('key', 'email_verification')->exists()) {
            LoginSetup::create([
                'key' => 'email_verification',
                'value' => $emailVerification
            ]);
        }

        if (!LoginSetup::where('key', 'phone_verification')->exists()) {
            LoginSetup::create([
                'key' => 'phone_verification',
                'value' => $phoneVerification
            ]);
        }

        if (!LoginSetup::where('key', 'login_options')->exists())  {
            LoginSetup::create([
                'key' => 'login_options',
                'value' => json_encode([
                    'manual_login' => 1,
                    'otp_login' => 0,
                    'social_media_login' => 0
                ]),
            ]);
        }
        if (!LoginSetup::where('key', 'social_media_for_login')->exists())  {
            LoginSetup::create([
                'key' => 'social_media_for_login',
                'value' => json_encode([
                    'google' => 0,
                    'facebook' => 0,
                    'apple' =>0
                ]),
            ]);
        }

        $recaptcha = Helpers::get_business_settings('recaptcha');
        if (isset($recaptcha) && isset($recaptcha['status'], $recaptcha['site_key'], $recaptcha['secret_key']) && $recaptcha['status'] == 1) {
            DB::table('business_settings')->updateOrInsert(['key' => 'recaptcha'], [
                'value' => json_encode([
                    'status' => 0,
                    'site_key' => $recaptcha['site_key'],
                    'secret_key' => $recaptcha['secret_key'],
                ]),
            ]);
        }

        $fixedDeliveryCharge = Helpers::get_business_settings('delivery_charge');
        $branchIds = Branch::pluck('id')->toArray();
        $existingBranchIds = DeliveryChargeSetup::pluck('branch_id')->toArray();

        foreach($branchIds as $branchId) {
            if (!in_array($branchId, $existingBranchIds)) {
                DeliveryChargeSetup::updateOrCreate([
                    'branch_id' => $branchId
                ], [
                    'delivery_charge_type' => 'fixed',
                    'fixed_delivery_charge' => $fixedDeliveryCharge,
                ]);
            }
        }

        if (!BusinessSetting::where(['key' => 'google_map_status'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'google_map_status'], [
                'value' => 1
            ]);
        }

        if (!BusinessSetting::where(['key' => 'admin_order_notification'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'admin_order_notification'], [
                'value' => 1
            ]);
        }

        if (!BusinessSetting::where(['key' => 'admin_order_notification_type'])->first()) {
            DB::table('business_settings')->updateOrInsert(['key' => 'admin_order_notification_type'], [
                'value' => "manual"
            ]);
        }

        return redirect('/admin/auth/login');
    }

    private function set_payment_data(){
        try{
            $gateway= [
                'ssl_commerz_payment',
                'razor_pay',
                'paypal',
                'stripe',
                'senang_pay',
                'paystack',
                'bkash',
                'paymob',
                'flutterwave',
                'mercadopago',
            ];


            $data= BusinessSetting::whereIn('key',$gateway)->pluck('value','key')->toArray();

            foreach($data as $key => $value){
                $gateway=$key;
                if($key == 'ssl_commerz_payment' ){
                    $gateway='ssl_commerz';
                }
                if($key == 'paymob' ){
                    $gateway='paymob_accept';
                }

                $decoded_value= json_decode($value , true);
                $data= ['gateway' => $gateway ,
                    'mode' =>  isset($decoded_value['status']) == 1  ?  'live': 'test'
                ];

                $additional_data =[];

                if ($gateway == 'ssl_commerz') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'store_id' => $decoded_value['store_id'],
                        'store_password' => $decoded_value['store_password'],
                    ];
                } elseif ($gateway == 'paypal') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'client_id' => $decoded_value['paypal_client_id'],
                        'client_secret' => $decoded_value['paypal_secret'],
                    ];
                } elseif ($gateway == 'stripe') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'api_key' => $decoded_value['api_key'],
                        'published_key' => $decoded_value['published_key'],
                    ];
                } elseif ($gateway == 'razor_pay') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'api_key' => $decoded_value['razor_key'],
                        'api_secret' => $decoded_value['razor_secret'],
                    ];
                } elseif ($gateway == 'senang_pay') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'callback_url' => null,
                        'secret_key' => $decoded_value['secret_key'],
                        'merchant_id' => $decoded_value['merchant_id'],
                    ];
                } elseif ($gateway == 'paystack') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'callback_url' => $decoded_value['paymentUrl'],
                        'public_key' => $decoded_value['publicKey'],
                        'secret_key' => $decoded_value['secretKey'],
                        'merchant_email' => $decoded_value['merchantEmail'],
                    ];
                } elseif ($gateway == 'paymob_accept') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'callback_url' => null,
                        'api_key' => $decoded_value['api_key'],
                        'iframe_id' => $decoded_value['iframe_id'],
                        'integration_id' => $decoded_value['integration_id'],
                        'hmac' => $decoded_value['hmac'],
                    ];
                } elseif ($gateway == 'mercadopago') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'access_token' => $decoded_value['access_token'],
                        'public_key' => $decoded_value['public_key'],
                    ];
                } elseif ($gateway == 'flutterwave') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'secret_key' => $decoded_value['secret_key'],
                        'public_key' => $decoded_value['public_key'],
                        'hash' => $decoded_value['hash'],
                    ];
                } elseif ($gateway == 'bkash') {
                    $additional_data = [
                        'status' => $decoded_value['status'],
                        'app_key' => $decoded_value['api_key'],
                        'app_secret' => $decoded_value['api_secret'],
                        'username' => $decoded_value['username'],
                        'password' => $decoded_value['password'],
                    ];
                }

                $credentials= json_encode(array_merge($data, $additional_data));

                $payment_additional_data=['gateway_title' => ucfirst(str_replace('_',' ',$gateway)),
                    'gateway_image' => null];

                DB::table('addon_settings')->updateOrInsert(['key_name' => $gateway, 'settings_type' => 'payment_config'], [
                    'key_name' => $gateway,
                    'live_values' => $credentials,
                    'test_values' => $credentials,
                    'settings_type' => 'payment_config',
                    'mode' => isset($decoded_value['status']) == 1  ?  'live': 'test',
                    'is_active' => isset($decoded_value['status']) == 1  ?  1: 0 ,
                    'additional_data' => json_encode($payment_additional_data),
                ]);
            }
        } catch (\Exception $exception) {
            Toastr::error('Database import failed! try again');
            return true;
        }
        return true;
    }

    private function set_sms_data(){
        try{
            $sms_gateway= ['twilio_sms', 'nexmo_sms', 'msg91_sms', '2factor_sms', 'signalwire_sms'];

            $data= BusinessSetting::whereIn('key',$sms_gateway)->pluck('value','key')->toArray();
            foreach($data as $key => $value){
                $decoded_value= json_decode($value , true);

                if ($key == 'twilio_sms') {
                    $sms_gateway='twilio';
                    $additional_data = [
                        'status' => data_get($decoded_value,'status',null),
                        'sid' => data_get($decoded_value,'sid',null),
                        'messaging_service_sid' =>  data_get($decoded_value,'messaging_service_id',null),
                        'token' => data_get($decoded_value,'token',null),
                        'from' =>data_get($decoded_value,'from',null),
                        'otp_template' => data_get($decoded_value,'otp_template',null),
                    ];
                } elseif ($key == 'nexmo_sms') {
                    $sms_gateway='nexmo';
                    $additional_data = [
                        'status' => data_get($decoded_value,'status',null),
                        'api_key' => data_get($decoded_value,'api_key',null),
                        'api_secret' =>  data_get($decoded_value,'api_secret',null),
                        'token' => data_get($decoded_value,'token',null),
                        'from' =>  data_get($decoded_value,'from',null),
                        'otp_template' =>  data_get($decoded_value,'otp_template',null),
                    ];
                } elseif ($key == '2factor_sms') {
                    $sms_gateway='2factor';
                    $additional_data = [
                        'status' => data_get($decoded_value,'status',null),
                        'api_key' => data_get($decoded_value,'api_key',null),
                    ];
                } elseif ($key == 'msg91_sms') {
                    $sms_gateway='msg91';
                    $additional_data = [
                        'status' => data_get($decoded_value,'status',null),
                        'template_id' =>  data_get($decoded_value,'template_id',null),
                        'auth_key' =>  data_get($decoded_value,'authkey',null),
                    ];
                }elseif ($key == 'signalwire_sms') {
                    $sms_gateway='signal_wire';
                    $additional_data = [
                        'status' => data_get($decoded_value,'status',null),
                        'project_id' =>  data_get($decoded_value,'project_id',null),
                        'token' => data_get($decoded_value,'token',null),
                        'space_url' => data_get($decoded_value,'space_url',null),
                        'from' =>  data_get($decoded_value,'from',null),
                        'otp_template' =>  data_get($decoded_value,'otp_template',null),
                    ];
                }


                $data= [
                    'gateway' => $sms_gateway ,
                    'mode' =>  isset($decoded_value['status']) == 1  ?  'live': 'test'
                ];
                $credentials= json_encode(array_merge($data, $additional_data));

                DB::table('addon_settings')->updateOrInsert(['key_name' => $sms_gateway, 'settings_type' => 'sms_config'], [
                    'key_name' => $sms_gateway,
                    'live_values' => $credentials,
                    'test_values' => $credentials,
                    'settings_type' => 'sms_config',
                    'mode' => isset($decoded_value['status']) == 1  ?  'live': 'test',
                    'is_active' => isset($decoded_value['status']) == 1  ?  1: 0 ,
                ]);
            }
        } catch (\Exception $exception) {
            Toastr::error('Database import failed! try again');
            return true;
        }
        return true;
    }
}
