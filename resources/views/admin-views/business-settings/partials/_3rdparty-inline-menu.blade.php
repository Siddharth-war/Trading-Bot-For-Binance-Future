<div class="mt-5 mb-5">
    <div class="inline-page-menu my-4">
        <ul class="list-unstyled">
            <li class="{{Request::is('admin/business-settings/web-app/third-party/payment-method')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.payment-method')}}">{{translate('Payment_Methods')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/mail-config')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.mail-config')}}">{{translate('Mail_Config')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/sms-module')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.sms-module')}}">{{translate('SMS_Config')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/map-api-settings')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.third-party.map_api_settings')}}">{{translate('Google_Map_APIs')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/recaptcha')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.third-party.recaptcha_index')}}">{{translate('Recaptcha')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/social-login')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.third-party.social-login')}}">{{translate('Social_Login')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/chat')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.third-party.chat')}}">{{translate('chat')}}</a></li>
            <li class="{{Request::is('admin/business-settings/web-app/third-party/firebase-otp-verification')? 'active': ''}}"><a href="{{route('admin.business-settings.web-app.third-party.firebase-otp-verification')}}">{{translate('Firebase_Auth_verification')}}</a></li>
        </ul>
    </div>
</div>
