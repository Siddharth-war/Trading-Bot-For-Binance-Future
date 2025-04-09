<?php

namespace App\CentralLogics;

use App\CPU\ImageManager;
use App\Model\AddOn;
use App\Model\Branch;
use App\Model\BusinessSetting;
use App\Model\Currency;
use App\Model\DMReview;
use App\Model\Order;
use App\Model\Product;
use App\Model\ProductByBranch;
use App\Model\Review;
use App\Models\DeliveryChargeByArea;
use App\Models\LoginSetup;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Helpers
{
    public static function error_processor($validator)
    {
        $err_keeper = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            $err_keeper[] = ['code' => $index, 'message' => $error[0]];
        }
        return $err_keeper;
    }

    public static function combinations($arrays)
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, [$property => $property_value]);
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    public static function variation_price($product, $variation)
    {
        if (empty(json_decode($variation, true))) {
            $result = $product['price'];
        } else {
            $match = json_decode($variation, true)[0];
            $result = 0;
            foreach (json_decode($product['variations'], true) as $property => $value) {
                if ($value['type'] == $match['type']) {
                    $result = $value['price'];
                }
            }
        }
        return self::set_price($result);
    }

    //get new variation price calculation for pos
    public static function new_variation_price($product, $variations)
    {
        $match = $variations;
        $result = 0;

        foreach($product as $product_variation){
            foreach($product_variation['values'] as $option){
                foreach($match as $variation){
                    if($product_variation['name'] == $variation['name'] && isset($variation['values']) && in_array($option['label'], $variation['values']['label'])){
                        $result += $option['optionPrice'];
                    }
                }
            }
        }
        return $result;
    }

    //new variation price calculation for order
    public static function get_varient(array $product_variations, array $variations)
    {
        $result = [];
        $variation_price = 0;

        foreach($variations as $k=> $variation){
            foreach($product_variations as  $product_variation){
                if( isset($variation['values']) && isset($product_variation['values']) && $product_variation['name'] == $variation['name']  ){
                    $result[$k] = $product_variation;
                    $result[$k]['values'] = [];
                    foreach($product_variation['values'] as $key=> $option){
                        if(in_array($option['label'], $variation['values']['label'])){
                            $result[$k]['values'][] = $option;
                            $variation_price += $option['optionPrice'];
                        }
                    }
                }
            }
        }

        return ['price'=>$variation_price,'variations'=>$result];
    }

    public static function product_data_formatting($data, $multi_data = false)
    {
        $storage = [];

        if ($multi_data == true) {
            foreach ($data as $item) {

                $variations = [];
                $item['category_ids'] = json_decode($item['category_ids']);
                $item['attributes'] = json_decode($item['attributes']);
                $item['choice_options'] = json_decode($item['choice_options']);
                $item['add_ons'] = AddOn::whereIn('id', json_decode($item['add_ons']))->get();

                $item['variations'] = json_decode($item['variations'], true);

                if (count($item['translations'])) {
                    foreach ($item['translations'] as $translation) {
                        if ($translation->key == 'name') {
                            $item['name'] = $translation->value;
                        }
                        if ($translation->key == 'description') {
                            $item['description'] = $translation->value;
                        }
                    }
                }
                unset($item['translations']);
                $storage[] = $item;
            }
            $data = $storage;
        } else {
            $data_addons = $data['add_ons'];
            $addon_ids = [];
            if(gettype($data_addons) != 'array') {
                $addon_ids = json_decode($data_addons);

            } elseif(gettype($data_addons) == 'array' && isset($data_addons[0]['id'])) {
                foreach($data_addons as $addon) {
                    $addon_ids[] = $addon['id'];
                }

            } else {
                $addon_ids = $data_addons;
            }

            $variations = [];
            $data['category_ids'] = gettype($data['category_ids']) != 'array' ? json_decode($data['category_ids']) : $data['category_ids'];
            $data['attributes'] = gettype($data['attributes']) != 'array' ? json_decode($data['attributes']) : $data['attributes'];
            $data['choice_options'] = gettype($data['choice_options']) != 'array' ? json_decode($data['choice_options']) : $data['choice_options'];

            //$data['add_ons'] = AddOn::whereIn('id', $addon_ids)->get();
            //$data['variations'] = json_decode($data['variations'], true);

            //variation server relate data formating
            $data['add_ons'] = AddOn::whereIn('id', $addon_ids)->get()->toArray();
            $data['variations'] = gettype($data['variations']) == 'array' ? $data['variations'] : json_decode($data['variations'], true);


            if (count($data['translations']) > 0) {
                foreach ($data['translations'] as $translation) {
                    if ($translation->key == 'name') {
                        $data['name'] = $translation->value;
                    }
                    if ($translation->key == 'description') {
                        $data['description'] = $translation->value;
                    }
                }
            }
        }

        return $data;
    }

    public static function order_data_formatting($data, $multi_data = false)
    {
        $storage = [];
        if ($multi_data) {
            foreach ($data as $item) {
                $item['add_on_ids'] = json_decode($item['add_on_ids']);
                $storage[] = $item;
            }
            $data = $storage;
        } else {
            $data['add_on_ids'] = json_decode($data['add_on_ids']);

            foreach ($data->details as $detail) {
                $detail->product_details = gettype($detail->product_details) != 'array' ? json_decode($detail->product_details) : $detail->product_details;

                $detail->product_details->add_ons = gettype($detail->product_details->add_ons) != 'array' ? json_decode($detail->product_details->add_ons) : $detail->product_details->add_ons;
                $detail->product_details->variations = gettype($detail->product_details->variations) != 'array' ? json_decode($detail->product_details->variations) : $detail->product_details->variations;
                $detail->product_details->attributes = gettype($detail->product_details->attributes) != 'array' ? json_decode($detail->product_details->attributes) : $detail->product_details->attributes;
                $detail->product_details->category_ids = gettype($detail->product_details->category_ids) != 'array' ? json_decode($detail->product_details->category_ids) : $detail->product_details->category_ids;
                $detail->product_details->choice_options = gettype($detail->product_details->choice_options) != 'array' ? json_decode($detail->product_details->choice_options) : $detail->product_details->choice_options;

                $detail->variation = gettype($detail->variation) != 'array' ? json_decode($detail->variation) : $detail->variation;
                $detail->add_on_ids = gettype($detail->add_on_ids) != 'array' ? json_decode($detail->add_on_ids) : $detail->add_on_ids;
                $detail->variant = gettype($detail->variant) != 'array' ? json_decode($detail->variant) : $detail->variant;
                $detail->add_on_qtys = gettype($detail->add_on_qtys) != 'array' ? json_decode($detail->add_on_qtys) : $detail->add_on_qtys;
            }
        }

        return $data;
    }

    public static function get_business_settings($name)
    {
        $config = null;
        $settings = Cache::rememberForever(CACHE_BUSINESS_SETTINGS_TABLE, function () {
            return BusinessSetting::all();
        });

        $data = $settings?->firstWhere('key', $name);
        if (isset($data)) {
            $config = json_decode($data['value'], true);
            if (is_null($config)) {
                $config = $data['value'];
            }
        }
        return $config;
    }

    public static function get_login_settings($name)
    {
        $config = null;
        $settings = Cache::rememberForever(CACHE_LOGIN_SETUP_TABLE, function () {
            return LoginSetup::all();
        });

        $data = $settings?->firstWhere('key', $name);
        if (isset($data)) {
            $config = json_decode($data['value'], true);
            if (is_null($config)) {
                $config = $data['value'];
            }
        }
        return $config;
    }

    public static function currency_code()
    {
        $currency_code = BusinessSetting::where(['key' => 'currency'])->first()->value;
        return $currency_code;
    }

    public static function currency_symbol()
    {
        $currency_symbol = Currency::where(['currency_code' => Helpers::currency_code()])->first()->currency_symbol;
        return $currency_symbol;
    }

    public static function set_symbol($amount)
    {
        $decimal_point_settings = Helpers::get_business_settings('decimal_point_settings');
        $position = Helpers::get_business_settings('currency_symbol_position');
        if (!is_null($position) && $position == 'left') {
            $string = self::currency_symbol() . '' . number_format($amount, $decimal_point_settings);
        } else {
            $string = number_format($amount, $decimal_point_settings) . '' . self::currency_symbol();
        }
        return $string;
    }

    public static function set_price($amount)
    {
        $decimal_point_settings = Helpers::get_business_settings('decimal_point_settings');
        $amount = number_format($amount, $decimal_point_settings, '.', '');

        return $amount;
    }

    /**
     * @param array|null $data
     * @return false|PromiseInterface|Response
     */
    public static function sendNotificationToHttp(array|null $data): bool|PromiseInterface|Response
    {
        $config = self::get_business_settings('push_notification_service_file_content');
        $key = (array)$config;
        if (isset($key['project_id'])){
            $url = 'https://fcm.googleapis.com/v1/projects/'.$key['project_id'].'/messages:send';
            $headers = [
                'Authorization' => 'Bearer ' . self::getAccessToken($key),
                'Content-Type' => 'application/json',
            ];
            try {
                return Http::withHeaders($headers)->post($url, $data);
            }catch (\Exception $exception){
                return false;
            }
        }
        return false;
    }

    public static function getAccessToken($key):String
    {
        $jwtToken = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => time() + 3600,
            'iat' => time(),
        ];
        $jwtHeader = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $jwtPayload = base64_encode(json_encode($jwtToken));
        $unsignedJwt = $jwtHeader . '.' . $jwtPayload;
        openssl_sign($unsignedJwt, $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        $jwt = $unsignedJwt . '.' . base64_encode($signature);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);
        return $response->json('access_token');
    }

    public static function send_push_notif_to_device($fcm_token, $data, $isDeliverymanAssigned = false)
    {
        $postData = [
            'message' => [
                "token" => $fcm_token,
                "data" => [
                    "title" => (string)$data['title'],
                    "body" => (string)$data['description'],
                    "image" => (string)$data['image'],
                    "order_id" => (string)$data['order_id'],
                    "type" => (string)$data['type'],
                    "is_deliveryman_assigned" => $isDeliverymanAssigned ? "1" : "0",
                ],
                "notification" => [
                    'title' => (string)$data['title'],
                    'body' => (string)$data['description'],
                ],
            ]
        ];
        return self::sendNotificationToHttp($postData);
    }

    public static function send_push_notif_to_topic($data, $topic, $type, $web_push_link = null, $isNotificationPayloadRemove = false)
    {
        $postData = [
            'message' => [
                "topic" => $topic,
                "data" => [
                    "title" => (string)$data['title'],
                    "body" => (string)$data['description'],
                    "order_id" => (string)$data['order_id'],
                    "order_status" => (string)($data['order_status'] ?? ''),
                    "type" => (string)$type,
                    "image" => (string)$data['image'],
                    "click_action" => $web_push_link ? (string)$web_push_link : '',
                    "is_background_sound_active" => $isNotificationPayloadRemove ? "1" : "0",
                ],
                "notification" => [
                    "title" => (string)$data['title'],
                    "body" => (string)$data['description'],
                    "image" => (string)$data['image']
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "notification.wav"
                        ]
                    ]
                ],
            ]
        ];

      /*  if (!$isNotificationPayloadRemove) {
            $postData['message']['notification'] = [
                "title" => (string)$data['title'],
                "body" => (string)$data['description'],
                "image" => (string)$data['image']
            ];
        }*/

        return self::sendNotificationToHttp($postData);
    }

    public static function rating_count($product_id, $rating)
    {
        return Review::where(['product_id' => $product_id, 'rating' => $rating])->count();
    }

    public static function dm_rating_count($deliveryman_id, $rating)
    {
        return DMReview::where(['delivery_man_id' => $deliveryman_id, 'rating' => $rating])->count();
    }

    public static function tax_calculate($product, $price)
    {
        if ($product['tax_type'] == 'percent') {
            $price_tax = ($price / 100) * $product['tax'];
        } else {
            $price_tax = $product['tax'];
        }
        return self::set_price($price_tax);
    }

    public static function discount_calculate($product, $price)
    {
        if ($product['discount_type'] == 'percent') {
            $price_discount = ($price / 100) * $product['discount'];
        } else {
            $price_discount = $product['discount'];
        }
        return self::set_price($price_discount);
    }

    public static function max_earning()
    {
        $data = Order::where(['order_status' => 'delivered'])->select('id', 'created_at', 'order_amount')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $max = 0;
        foreach ($data as $month) {
            $count = 0;
            foreach ($month as $order) {
                $count += $order['order_amount'];
            }
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }

    public static function max_orders()
    {
        $data = Order::select('id', 'created_at')
            ->get()
            ->groupBy(function ($date) {
                return Carbon::parse($date->created_at)->format('m');
            });

        $max = 0;
        foreach ($data as $month) {
            $count = 0;
            foreach ($month as $order) {
                $count += 1;
            }
            if ($count > $max) {
                $max = $count;
            }
        }
        return $max;
    }

    public static function order_status_update_message($status)
    {
        if ($status == 'pending') {
            $data = self::get_business_settings('order_pending_message');
        } elseif ($status == 'confirmed') {
            $data = self::get_business_settings('order_confirmation_msg');
        } elseif ($status == 'processing') {
            $data = self::get_business_settings('order_processing_message');
        } elseif ($status == 'out_for_delivery') {
            $data = self::get_business_settings('out_for_delivery_message');
        } elseif ($status == 'delivered') {
            $data = self::get_business_settings('order_delivered_message');
        } elseif ($status == 'delivery_boy_delivered') {
            $data = self::get_business_settings('delivery_boy_delivered_message');
        } elseif ($status == 'del_assign') {
            $data = self::get_business_settings('delivery_boy_assign_message');
        } elseif ($status == 'ord_start') {
            $data = self::get_business_settings('delivery_boy_start_message');
        } elseif ($status == 'returned') {
            $data = self::get_business_settings('returned_message');
        } elseif ($status == 'failed') {
            $data = self::get_business_settings('failed_message');
        } elseif ($status == 'canceled') {
            $data = self::get_business_settings('canceled_message');
        } elseif ($status == 'customer_notify_message') {
            $data = self::get_business_settings('customer_notify_message');
        } elseif ($status == 'customer_notify_message_for_time_change') {
            $data = self::get_business_settings('customer_notify_message_for_time_change');
        } elseif ($status == 'add_wallet_message') {
            $data = self::get_business_settings('add_wallet_message');
        } elseif ($status == 'add_wallet_bonus_message') {
            $data = self::get_business_settings('add_wallet_bonus_message');
        } else {
            $data['status'] = 0;
            $data['message'] = "";
//            $data = '{"status":"0","message":""}';

        }

        if ($data == null || (array_key_exists('status', $data) && $data['status'] == 0)) {
            return 0;
        }

        return $data['message'];
    }

    public static function day_part()
    {
        $part = "";
        $morning_start = date("h:i:s", strtotime("5:00:00"));
        $afternoon_start = date("h:i:s", strtotime("12:01:00"));
        $evening_start = date("h:i:s", strtotime("17:01:00"));
        $evening_end = date("h:i:s", strtotime("21:00:00"));

        if (time() >= $morning_start && time() < $afternoon_start) {
            $part = "morning";
        } elseif (time() >= $afternoon_start && time() < $evening_start) {
            $part = "afternoon";
        } elseif (time() >= $evening_start && time() <= $evening_end) {
            $part = "evening";
        } else {
            $part = "night";
        }

        return $part;
    }

    public static function env_update($key, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
            ));
        }
    }

    public static function env_key_replace($key_from, $key_to, $value)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                $key_from . '=' . env($key_from), $key_to . '=' . $value, file_get_contents($path)
            ));
        }
    }

    public static function remove_dir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") Helpers::remove_dir($dir . "/" . $object); else unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function get_language_name($key)
    {
        $languages = array(
            "af" => "Afrikaans",
            "sq" => "Albanian - shqip",
            "am" => "Amharic - አማርኛ",
            "ar" => "Arabic - العربية",
            "an" => "Aragonese - aragonés",
            "hy" => "Armenian - հայերեն",
            "ast" => "Asturian - asturianu",
            "az" => "Azerbaijani - azərbaycan dili",
            "eu" => "Basque - euskara",
            "be" => "Belarusian - беларуская",
            "bn" => "Bengali - বাংলা",
            "bs" => "Bosnian - bosanski",
            "br" => "Breton - brezhoneg",
            "bg" => "Bulgarian - български",
            "ca" => "Catalan - català",
            "ckb" => "Central Kurdish - کوردی (دەستنوسی عەرەبی)",
            "zh" => "Chinese - 中文",
            "zh-HK" => "Chinese (Hong Kong) - 中文（香港）",
            "zh-CN" => "Chinese (Simplified) - 中文（简体）",
            "zh-TW" => "Chinese (Traditional) - 中文（繁體）",
            "co" => "Corsican",
            "hr" => "Croatian - hrvatski",
            "cs" => "Czech - čeština",
            "da" => "Danish - dansk",
            "nl" => "Dutch - Nederlands",
            "en" => "English",
            "en-AU" => "English (Australia)",
            "en-CA" => "English (Canada)",
            "en-IN" => "English (India)",
            "en-NZ" => "English (New Zealand)",
            "en-ZA" => "English (South Africa)",
            "en-GB" => "English (United Kingdom)",
            "en-US" => "English (United States)",
            "eo" => "Esperanto - esperanto",
            "et" => "Estonian - eesti",
            "fo" => "Faroese - føroyskt",
            "fil" => "Filipino",
            "fi" => "Finnish - suomi",
            "fr" => "French - français",
            "fr-CA" => "French (Canada) - français (Canada)",
            "fr-FR" => "French (France) - français (France)",
            "fr-CH" => "French (Switzerland) - français (Suisse)",
            "gl" => "Galician - galego",
            "ka" => "Georgian - ქართული",
            "de" => "German - Deutsch",
            "de-AT" => "German (Austria) - Deutsch (Österreich)",
            "de-DE" => "German (Germany) - Deutsch (Deutschland)",
            "de-LI" => "German (Liechtenstein) - Deutsch (Liechtenstein)",
            "de-CH" => "German (Switzerland) - Deutsch (Schweiz)",
            "el" => "Greek - Ελληνικά",
            "gn" => "Guarani",
            "gu" => "Gujarati - ગુજરાતી",
            "ha" => "Hausa",
            "haw" => "Hawaiian - ʻŌlelo Hawaiʻi",
            "he" => "Hebrew - עברית",
            "hi" => "Hindi - हिन्दी",
            "hu" => "Hungarian - magyar",
            "is" => "Icelandic - íslenska",
            "id" => "Indonesian - Indonesia",
            "ia" => "Interlingua",
            "ga" => "Irish - Gaeilge",
            "it" => "Italian - italiano",
            "it-IT" => "Italian (Italy) - italiano (Italia)",
            "it-CH" => "Italian (Switzerland) - italiano (Svizzera)",
            "ja" => "Japanese - 日本語",
            "kn" => "Kannada - ಕನ್ನಡ",
            "kk" => "Kazakh - қазақ тілі",
            "km" => "Khmer - ខ្មែរ",
            "ko" => "Korean - 한국어",
            "ku" => "Kurdish - Kurdî",
            "ky" => "Kyrgyz - кыргызча",
            "lo" => "Lao - ລາວ",
            "la" => "Latin",
            "lv" => "Latvian - latviešu",
            "ln" => "Lingala - lingála",
            "lt" => "Lithuanian - lietuvių",
            "mk" => "Macedonian - македонски",
            "ms" => "Malay - Bahasa Melayu",
            "ml" => "Malayalam - മലയാളം",
            "mt" => "Maltese - Malti",
            "mr" => "Marathi - मराठी",
            "mn" => "Mongolian - монгол",
            "ne" => "Nepali - नेपाली",
            "no" => "Norwegian - norsk",
            "nb" => "Norwegian Bokmål - norsk bokmål",
            "nn" => "Norwegian Nynorsk - nynorsk",
            "oc" => "Occitan",
            "or" => "Oriya - ଓଡ଼ିଆ",
            "om" => "Oromo - Oromoo",
            "ps" => "Pashto - پښتو",
            "fa" => "Persian - فارسی",
            "pl" => "Polish - polski",
            "pt" => "Portuguese - português",
            "pt-BR" => "Portuguese (Brazil) - português (Brasil)",
            "pt-PT" => "Portuguese (Portugal) - português (Portugal)",
            "pa" => "Punjabi - ਪੰਜਾਬੀ",
            "qu" => "Quechua",
            "ro" => "Romanian - română",
            "mo" => "Romanian (Moldova) - română (Moldova)",
            "rm" => "Romansh - rumantsch",
            "ru" => "Russian - русский",
            "gd" => "Scottish Gaelic",
            "sr" => "Serbian - српски",
            "sh" => "Serbo-Croatian - Srpskohrvatski",
            "sn" => "Shona - chiShona",
            "sd" => "Sindhi",
            "si" => "Sinhala - සිංහල",
            "sk" => "Slovak - slovenčina",
            "sl" => "Slovenian - slovenščina",
            "so" => "Somali - Soomaali",
            "st" => "Southern Sotho",
            "es" => "Spanish - español",
            "es-AR" => "Spanish (Argentina) - español (Argentina)",
            "es-419" => "Spanish (Latin America) - español (Latinoamérica)",
            "es-MX" => "Spanish (Mexico) - español (México)",
            "es-ES" => "Spanish (Spain) - español (España)",
            "es-US" => "Spanish (United States) - español (Estados Unidos)",
            "su" => "Sundanese",
            "sw" => "Swahili - Kiswahili",
            "sv" => "Swedish - svenska",
            "tg" => "Tajik - тоҷикӣ",
            "ta" => "Tamil - தமிழ்",
            "tt" => "Tatar",
            "te" => "Telugu - తెలుగు",
            "th" => "Thai - ไทย",
            "ti" => "Tigrinya - ትግርኛ",
            "to" => "Tongan - lea fakatonga",
            "tr" => "Turkish - Türkçe",
            "tk" => "Turkmen",
            "tw" => "Twi",
            "uk" => "Ukrainian - українська",
            "ur" => "Urdu - اردو",
            "ug" => "Uyghur",
            "uz" => "Uzbek - o‘zbek",
            "vi" => "Vietnamese - Tiếng Việt",
            "wa" => "Walloon - wa",
            "cy" => "Welsh - Cymraeg",
            "fy" => "Western Frisian",
            "xh" => "Xhosa",
            "yi" => "Yiddish",
            "yo" => "Yoruba - Èdè Yorùbá",
            "zu" => "Zulu - isiZulu",
        );
        return array_key_exists($key, $languages) ? $languages[$key] : $key;
    }

    public static function language_load()
    {
        if (\session()->has('language_settings')) {
            $language = \session('language_settings');
        } else {
            $language = BusinessSetting::where('key', 'language')->first();
            \session()->put('language_settings', $language);
        }
        return $language;
    }

    public static function upload(string $dir, string $format, $image = null)
    {
        if ($image != null) {
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->put($dir . $imageName, file_get_contents($image));
        } else {
            $imageName = 'def.png';
        }

        return $imageName;
    }

    public static function update(string $dir, $old_image, string $format, $image = null)
    {
        if (Storage::disk('public')->exists($dir . $old_image)) {
            Storage::disk('public')->delete($dir . $old_image);
        }
        $imageName = Helpers::upload($dir, $format, $image);
        return $imageName;
    }

    public static function delete($full_path)
    {
        if (Storage::disk('public')->exists($full_path)) {
            Storage::disk('public')->delete($full_path);
        }
        return [
            'success' => 1,
            'message' => 'Removed successfully !'
        ];
    }

    public static function setEnvironmentValue($envKey, $envValue)
    {
        $envFile = app()->environmentFilePath();
        $str = file_get_contents($envFile);
        if (is_bool(env($envKey))) {
            $oldValue = var_export(env($envKey), true);
        } else {
            $oldValue = env($envKey);
        }
//        $oldValue = var_export(env($envKey), true);

        if (strpos($str, $envKey) !== false) {
            $str = str_replace("{$envKey}={$oldValue}", "{$envKey}={$envValue}", $str);

//            dd("{$envKey}={$envValue}");
//            dd($str);
        } else {
            $str .= "{$envKey}={$envValue}\n";
        }
        $fp = fopen($envFile, 'w');
        fwrite($fp, $str);
        fclose($fp);
        return $envValue;
    }

    public static function requestSender($request): array
    {
        $remove = array("http://", "https://", "www.");
        $url = str_replace($remove, "", url('/'));

        $post = [
            base64_decode('dXNlcm5hbWU=') => $request['username'],//un
            base64_decode('cHVyY2hhc2Vfa2V5') => $request['purchase_key'],//pk
            base64_decode('c29mdHdhcmVfaWQ=') => base64_decode(env(base64_decode('U09GVFdBUkVfSUQ='))),//sid
            base64_decode('ZG9tYWlu') => $url,
        ];

        //session()->put('domain', 'https://' . preg_replace("#^[^:/.]*[:/]+#i", "", $request['domain']));

        $ch = curl_init('https://check.6amtech.com/api/v1/domain-register');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);

        try {
            if (base64_decode(json_decode($response, true)['active'])) {
                return [
                    'active' => (int)base64_decode(json_decode($response, true)['active'])
                ];
            }
            return [
                'active' => 0
            ];
        } catch (\Exception $exception) {
            return [
                'active' => 1
            ];
        }
    }

    public static function getPagination()
    {
        $pagination_limit = Helpers::get_business_settings('pagination_limit');
        return $pagination_limit ?? 25;
    }

    public static function remove_invalid_charcaters($str)
    {
        return str_ireplace(['\'', '"', ';', '<', '>'], ' ', $str);
    }

    public static function get_delivery_charge($branchId, $distance = null, $selectedDeliveryArea = null, )
    {
        $branch = Branch::with(['delivery_charge_setup', 'delivery_charge_by_area'])
            ->where(['id' => $branchId])
            ->first(['id', 'name', 'status']);

        $deliveryType = $branch->delivery_charge_setup->delivery_charge_type ?? 'fixed';
        $deliveryType = $deliveryType === 'area' ? 'area' : ($deliveryType === 'distance' ? 'distance' : 'fixed');

        if($deliveryType == 'area'){
            $area = DeliveryChargeByArea::find($selectedDeliveryArea);
            $deliveryCharge = $area->delivery_charge ?? 0;
        }elseif($deliveryType == 'distance') {
            $minDeliveryCharge = $branch->delivery_charge_setup->minimum_delivery_charge;
            $shippingChargePerKM = $branch->delivery_charge_setup->delivery_charge_per_kilometer;
            $minDistanceForFreeDelivery = $branch->delivery_charge_setup->minimum_distance_for_free_delivery;

            if ($distance <= $minDistanceForFreeDelivery) {
                $deliveryCharge = 0;
            } else {
                $distanceDeliveryCharge = $shippingChargePerKM * $distance;
                $deliveryCharge = max($distanceDeliveryCharge, $minDeliveryCharge);
            }
        }else{
            $deliveryCharge = $branch->delivery_charge_setup->fixed_delivery_charge ?? 0;
        }
        return self::set_price($deliveryCharge);
    }


    public static function calculate_addon_price($addons, $add_on_qtys)
    {
        $add_ons_cost = 0;
        $data = [];
        if ($addons) {
            foreach ($addons as $key2 => $addon) {
                if ($add_on_qtys == null) {
                    $add_on_qty = 1;
                } else {
                    $add_on_qty = $add_on_qtys[$key2];
                }
                $data[] = $addon->id;
                $add_ons_cost += $addon['price'] * $add_on_qty;
            }
            return ['addons' => $data, 'total_add_on_price' => self::set_price($add_ons_cost)];
        }
        return null;
    }


    public static function get_default_language()
    {
        $data = self::get_business_settings('language');
        $default_lang = 'en';
        if ($data && array_key_exists('code', $data)) {
            foreach ($data as $lang) {
                if ($lang['default'] == true) {
                    $default_lang = $lang['code'];
                }
            }
        }

        return $default_lang;
    }

    public static function module_permission_check($mod_name)
    {
        $permission = auth('admin')->user()->role->module_access??null;
        if (isset($permission) && in_array($mod_name, (array)json_decode($permission)) == true) {
            return true;
        }

        if (auth('admin')->user()->admin_role_id == 1) {
            return true;
        }
        return false;
    }

    public static function file_remover(string $dir, $image)
    {
        if (!isset($image)) return true;

        if (Storage::disk('public')->exists($dir . $image)) Storage::disk('public')->delete($dir . $image);

        return true;
    }

    public static function order_details_formatter($details)
    {
        if ($details->count() > 0) {
            foreach ($details as $detail) {
                $detail['product_details'] = gettype($detail['product_details']) != 'array' ? (array) json_decode($detail['product_details'], true) : (array) $detail['product_details'];
                $detail['variation'] = gettype($detail['variation']) != 'array' ? (array) json_decode($detail['variation'], true) : (array) $detail['variation'];
                $detail['add_on_ids'] = gettype($detail['add_on_ids']) != 'array' ? (array) json_decode($detail['add_on_ids'], true) : (array) $detail['add_on_ids'];
                $detail['variant'] = gettype($detail['variant']) != 'array' ? (array) json_decode($detail['variant'], true) : (array) $detail['variant'];
                $detail['add_on_qtys'] = gettype($detail['add_on_qtys']) != 'array' ? (array) json_decode($detail['add_on_qtys'], true) : (array) $detail['add_on_qtys'];
                $detail['add_on_prices'] = gettype($detail['add_on_prices']) != 'array' ? (array) json_decode($detail['add_on_prices'], true) : (array) $detail['add_on_prices'];
                $detail['add_on_taxes'] = gettype($detail['add_on_taxes']) != 'array' ? (array) json_decode($detail['add_on_taxes'], true) : (array) $detail['add_on_taxes'];

                if(!isset($detail['reviews_count'])) {
                    $detail['review_count'] = Review::where(['order_id' => $detail['order_id'], 'product_id' => $detail['product_id']])->count();
                }

                $detail['product_details'] = Helpers::product_formatter($detail['product_details']);

                $product_availability = Product::where('id', $detail['product_id'])->first();
                $detail['is_product_available'] = isset($product_availability) ? 1 : 0;
            }
        }

        return $details;
    }

    public static function product_formatter($product)
    {
        $product['variations'] = gettype($product['variations']) != 'array' ? (array)json_decode($product['variations'], true) : (array)$product['variations'];
        $product['add_ons'] = gettype($product['add_ons']) != 'array' ? (array)json_decode($product['add_ons'], true) : (array)$product['add_ons'];
        $product['attributes'] = gettype($product['attributes']) != 'array' ? (array)json_decode($product['attributes'], true) : (array)$product['attributes'];
        $product['category_ids'] = gettype($product['category_ids']) != 'array' ? (array)json_decode($product['category_ids'], true) : (array)$product['category_ids'];
        $product['choice_options'] = gettype($product['choice_options']) != 'array' ? (array)json_decode($product['choice_options'], true) : (array)$product['choice_options'];

        try {
            $addons = [];
            foreach ($product['add_ons'] as $add_on_id) {
                $addon = AddOn::find($add_on_id);
                if (isset($addon)) {
                    $addons [] = $addon;
                }
            }
            $product['add_ons'] = $addons;

        } catch (\Exception $exception) {
            //
        }

        return $product;
    }

    public static function generate_referer_code() {
        $ref_code = Str::random('20');
        if (User::where('refer_code', '=', $ref_code)->exists()) {
            return self::generate_referer_code();
        }
        return $ref_code;
    }

    public static function update_daily_product_stock() {
        $currentDay = now()->day;
        $currentMonth = now()->month;
        $products = ProductByBranch::where(['stock_type' => 'daily'])->get();
        foreach ($products as $product){
            if ($currentDay != $product['updated_at']->day || $currentMonth != $product['updated_at']->month){
                $product['sold_quantity'] = 0;
                $product->save();
            }
        }
        return true;
    }

    public static function text_variable_data_format($value,$user_name=null,$restaurant_name=null,$delivery_man_name=null,$transaction_id=null,$order_id=null)
    {
        $data = $value;
        if ($value) {
            if($user_name){
                $data =  str_replace("{userName}", $user_name, $data);
            }

            if($restaurant_name){
                $data =  str_replace("{restaurantName}", $restaurant_name, $data);
            }

            if($delivery_man_name){
                $data =  str_replace("{deliveryManName}", $delivery_man_name, $data);
            }

            if($order_id){
                $data =  str_replace("{orderId}", $order_id, $data);
            }
        }
        return $data;
    }

    public static function order_status_message_key($status)
    {
        if ($status == 'pending') {
            $data = 'order_pending_message';
        } elseif ($status == 'confirmed') {
            $data = 'order_confirmation_msg';
        } elseif ($status == 'processing') {
            $data = 'order_processing_message';
        } elseif ($status == 'out_for_delivery') {
            $data = 'out_for_delivery_message';
        } elseif ($status == 'delivered') {
            $data = 'order_delivered_message';
        } elseif ($status == 'delivery_boy_delivered') {
            $data = 'delivery_boy_delivered_message';
        } elseif ($status == 'del_assign') {
            $data = 'delivery_boy_assign_message';
        } elseif ($status == 'ord_start') {
            $data = 'delivery_boy_start_message';
        } elseif ($status == 'returned') {
            $data = 'returned_message';
        } elseif ($status == 'failed') {
            $data = 'failed_message';
        } elseif ($status == 'canceled') {
            $data = 'canceled_message';
        } elseif ($status == 'customer_notify_message') {
            $data = 'customer_notify_message';
        } elseif ($status == 'customer_notify_message_for_time_change') {
            $data = 'customer_notify_message_for_time_change';
        } else {
            $data = $status;
        }

        return $data;
    }

    public static function onErrorImage($data, $src, $error_src ,$path)
    {
        if(isset($data) && strlen($data) >1 && Storage::disk('public')->exists($path.$data)){
            return $src;
        }
        return $error_src;
    }

}

function translate($key)
{
    $local = session()->has('local') ? session('local') : 'en';
    App::setLocale($local);
    $lang_array = include(base_path('resources/lang/' . $local . '/messages.php'));
    $processed_key = ucfirst(str_replace('_', ' ', Helpers::remove_invalid_charcaters($key)));
    if (!array_key_exists($key, $lang_array)) {
        $lang_array[$key] = $processed_key;
        $str = "<?php return " . var_export($lang_array, true) . ";";
        file_put_contents(base_path('resources/lang/' . $local . '/messages.php'), $str);
        $result = $processed_key;
    } else {
        $result = __('messages.' . $key);
    }
    return $result;
}
