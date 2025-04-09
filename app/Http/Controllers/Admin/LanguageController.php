<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class LanguageController extends Controller
{
    public function __construct(
        private BusinessSetting $business_setting
    )
    {}

    /**
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('admin-views.business-settings.language.index');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'code' => 'required',
        ],[
            'code' => translate('Country code select is required'),
        ]);

        $language = Helpers::get_business_settings('language');
        if (!isset($language)) {
            $this->business_setting->updateOrInsert(['key' => 'language'], [
                'value' => '[{"id":"1","name":"english","direction":"ltr","code":"en","status":1,"default":true}]'
            ]);
            $language = Helpers::get_business_settings('language');
        }

        $langArray = [];
        $codes = [];
        foreach ($language as $key => $data) {
            if ($data['code'] != $request['code']) {
                if (!array_key_exists('default', $data)) {
                    $default = array('default' => $data['code'] == 'en');
                    $data = array_merge($data, $default);
                }
                $langArray[] = $data;
                $codes[] = $data['code'];
            }
        }
        $codes[] = $request['code'];

        if (!file_exists(base_path('resources/lang/' . $request['code']))) {
            mkdir(base_path('resources/lang/' . $request['code']), 0777, true);
        }

        $langFile = fopen(base_path('resources/lang/' . $request['code'] . '/' . 'messages.php'), "w") or die("Unable to open file!");
        $read = file_get_contents(base_path('resources/lang/en/messages.php'));
        fwrite($langFile, $read);

        $langArray[] = [
            'id' => count($language) + 1,
            'name' => $request['name'],
            'code' => $request['code'],
            'direction' => 'ltr',
            'status' => 0,
            'default' => false,
        ];

        $this->business_setting->updateOrInsert(['key' => 'language'], [
            'value' => $langArray
        ]);

        Toastr::success(translate('Language Added!'));
        return back();
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function updateStatus(Request $request): mixed
    {
        $languages = BusinessSetting::where('key', 'language')->first();
        $language = json_decode($languages->value, true);
        $langArray = [];
        foreach ($language as $key => $data) {
            if ($data['code'] == $request['code']) {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => 'ltr',
                    'code' => $data['code'],
                    'status' => $data['status'] == 1 ? 0 : 1,
                    'default' => (array_key_exists('default', $data) ? $data['default'] : $data['code'] == 'en'),
                ];
            } else {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => 'ltr',
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => (array_key_exists('default', $data) ? $data['default'] : $data['code'] == 'en'),
                ];
            }
            $langArray[] = $lang;
        }

        $businessSetting = $this->business_setting->where('key', 'language')->update([
            'value' => $langArray
        ]);

        return $businessSetting;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateDefaultStatus(Request $request): RedirectResponse
    {
        $language = Helpers::get_business_settings('language');
        $langArray = [];

        foreach ($language as $key => $data) {
            if ($data['code'] == $request['code']) {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => 1,
                    'default' => true,
                ];
            } else {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => false,
                ];
            }
            $langArray[] = $lang;
        }

        $this->business_setting->where('key', 'language')->update([
            'value' => $langArray
        ]);

        Toastr::success(translate('Default Language Changed!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
        ]);

        $language = Helpers::get_business_settings('language');
        $langArray = [];
        foreach ($language as $key => $data) {
            if ($data['code'] == $request['code']) {
                $lang = [
                    'id' => $data['id'],
                    'name' => $request['name'],
                    'direction' => $request['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => $data['status'] ?? 0,
                    'default' => (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false)),
                ];
            } else {
                $lang = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => $data['status'],
                    'default' => (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false)),
                ];
            }
            $langArray[] = $lang;
        }
        $this->business_setting->where('key', 'language')->update([
            'value' => $langArray
        ]);

        Toastr::success(translate('Language updated!'));
        return back();
    }

    /**
     * @param $lang
     * @return Renderable
     */
    public function translate($lang): Renderable
    {
        $fullData = include(base_path('resources/lang/' . $lang . '/messages.php'));
        $lang_data = [];
        ksort($fullData);
        foreach ($fullData as $key => $data) {
            $lang_data[] = ['key' => $key, 'value' => $data];
        }

        return view('admin-views.business-settings.language.translate', compact('lang', 'lang_data'));
    }

    /**
     * @param Request $request
     * @param $lang
     * @return void
     */
    public function translateKeyRemove(Request $request, $lang): void
    {
        $fullData = include(base_path('resources/lang/' . $lang . '/messages.php'));
        unset($fullData[$request['key']]);
        $str = "<?php return " . var_export($fullData, true) . ";";
        file_put_contents(base_path('resources/lang/' . $lang . '/messages.php'), $str);
    }

    /**
     * @param Request $request
     * @param $lang
     * @return void
     */
    public function translateSubmit(Request $request, $lang): void
    {
        $fullData = include(base_path('resources/lang/' . $lang . '/messages.php'));
        $fullData[urldecode($request['key'])] = $request['value'];
        $str = "<?php return " . var_export($fullData, true) . ";";
        file_put_contents(base_path('resources/lang/' . $lang . '/messages.php'), $str);
    }

    /**
     * @param $lang
     * @return RedirectResponse
     */
    public function delete($lang): RedirectResponse
    {
        $language = Helpers::get_business_settings('language');

        $deleteDefault = false;
        foreach ($language as $key => $data) {
            if ($data['code'] == $lang && array_key_exists('default', $data) && $data['default'] == true) {
                $deleteDefault = true;
            }
        }

        $langArray = [];
        foreach ($language as $key => $data) {
            if ($data['code'] != $lang) {
                $lang_data = [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'direction' => $data['direction'] ?? 'ltr',
                    'code' => $data['code'],
                    'status' => ($deleteDefault == true && $data['code'] == 'en') ? 1 : $data['status'],
                    'default' => ($deleteDefault == true && $data['code'] == 'en') ? true : (array_key_exists('default', $data) ? $data['default'] : (($data['code'] == 'en') ? true : false)),
                ];
                $langArray[] = $lang_data;
            }
        }

        $this->business_setting->where('key', 'language')->update([
            'value' => $langArray
        ]);

        $dir = base_path('resources/lang/' . $lang);
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);

        Toastr::success(translate('Removed Successfully!'));
        return back();
    }

    /**
     * @param $local
     * @return RedirectResponse
     */
    public function lang($local): RedirectResponse
    {
        $direction = 'ltr';
        $language = Helpers::get_business_settings('language');

        foreach ($language as $key => $data) {
            if ($data['code'] == $local) {
                $direction = $data['direction'] ?? 'ltr';
            }
        }

        session()->forget('language_settings');
        Helpers::language_load();
        session()->put('local', $local);
        Session::put('direction', $direction);

        return redirect()->back();
    }
}
