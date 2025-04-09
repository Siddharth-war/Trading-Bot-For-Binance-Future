<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Models\LoginSetup;
use App\Models\Setting;
use App\Traits\HelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class LoginSetupController extends Controller
{
    use HelperTrait;
    public function __construct(
        private LoginSetup $loginSetup
    ){}

    public function loginSetup()
    {
        $loginOptionsValue = $this->loginSetup->where(['key' => 'login_options'])?->first()->value;
        $loginOptions = json_decode($loginOptionsValue);

        $socialMediaLoginValue = $this->loginSetup->where(['key' => 'social_media_for_login'])?->first()->value;
        $socialMediaLoginOptions = json_decode($socialMediaLoginValue);

        $emailVerification = (int) $this->loginSetup->where(['key' => 'email_verification'])?->first()->value ?? 0;
        $phoneVerification = (int) $this->loginSetup->where(['key' => 'phone_verification'])?->first()->value ?? 0;
        return view('admin-views.business-settings.login-setup', compact('emailVerification', 'phoneVerification', 'loginOptions', 'socialMediaLoginOptions'));
    }

    public function loginSetupUpdate(Request $request)
    {
        if ($request->has('otp_login')){
            if(!$request->has('phone_verification')){
                Toastr::error(translate('Please active Phone Verification when OTP Login is active.'));
                return back();
            }
        }

        if ($request->has('social_media_login')){
            if(!$request->has('google') && !$request->has('facebook')){
                Toastr::error(translate('Please active at lest one social media login option between google or facebook.'));
                return back();
            }
        }

       $this->InsertOrUpdateLoginData(['key' => 'login_options'], [
                'value' => json_encode([
                    'manual_login' => $request->has('manual_login') ? 1: 0,
                    'otp_login' => $request->has('otp_login') ? 1: 0,
                    'social_media_login' => $request->has('social_media_login') ? 1: 0,
                ])
            ]
        );
       $this->InsertOrUpdateLoginData(['key' => 'social_media_for_login'], [
                'value' => json_encode([
                    'google' => $request->has('google') ? 1: 0,
                    'facebook' => $request->has('facebook') ? 1: 0,
                    'apple' => $request->has('apple') ? 1: 0,
                ])
           ]
       );
        $this->InsertOrUpdateLoginData(['key' => 'email_verification'], [
                'value' => $request->has('email_verification') ? 1: 0,
            ]
        );
        $this->InsertOrUpdateLoginData(['key' => 'phone_verification'], [
                'value' => $request->has('phone_verification') ? 1: 0,
            ]
        );

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    public function checkActiveSMSGateway(Request $request) {
        $activeSMSGatewaysCount = $this->getActiveSMSGatewayCount();
        return response()->json($activeSMSGatewaysCount);
    }

    public function checkActiveSocialMedia(Request $request) {
        $appleLogin = BusinessSetting::where('key', 'apple_login')->first();
        $appleLoginService = json_decode($appleLogin->value, true);

        $clientId = $appleLoginService['client_id'];
        $teamId = $appleLoginService['team_id'];
        $keyId = $appleLoginService['key_id'];
        $serviceFile = $appleLoginService['service_file'];

        $apple = 1;
        if ($clientId == null || $teamId == null || $keyId == null || $serviceFile == null ){
            $apple = 0;
        }

        return response()->json([
            'apple' => $apple,
        ]);
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    private function InsertOrUpdateLoginData($key, $value): void
    {
        $loginSetup = $this->loginSetup->where(['key' => $key['key']])->first();
        if ($loginSetup) {
            $loginSetup->value = $value['value'];
            $loginSetup->save();
        } else {
            $this->loginSetup->create($key, $value);
        }
    }
}
