<div class="mb-5 mt-5">
    <ul class="nav nav-tabs border-0 mb-3">
        <li class="nav-item">
            <a class="nav-link {{Request::is('admin/business-settings/web-app/system-setup/language*')? 'active' : ''}}" href="{{route('admin.business-settings.web-app.system-setup.language.index')}}">
                {{translate('Language Setup')}}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{Request::is('admin/business-settings/web-app/system-setup/app-setting*')? 'active' : ''}}" href="{{route('admin.business-settings.web-app.system-setup.app_setting')}}">
                {{translate('App Settings')}}
            </a>
        </li>
{{--        <li class="nav-item">--}}
{{--            <a class="nav-link {{Request::is('admin/business-settings/web-app/system-setup/firebase-message-config*')? 'active' : ''}}" href="{{route('admin.business-settings.web-app.system-setup.firebase_message_config_index')}}">--}}
{{--                {{translate('Firebase Configuration')}}--}}
{{--            </a>--}}
{{--        </li>--}}
        <li class="nav-item">
            <a class="nav-link {{Request::is('admin/business-settings/web-app/system-setup/db-index*')? 'active' : ''}}"  href="{{route('admin.business-settings.web-app.system-setup.db-index')}}">
                {{translate('Clean Database')}}
            </a>
        </li>
    </ul>
</div>
