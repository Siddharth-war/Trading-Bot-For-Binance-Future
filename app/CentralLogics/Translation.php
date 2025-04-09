<?php

use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\App;

if(!function_exists('translate')) {
    function  translate($key)
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
}
