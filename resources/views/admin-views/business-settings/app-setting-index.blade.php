@extends('layouts.admin.app')

@section('title', translate('App Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/app.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('system_setup')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._system-settings-inline-menu')

        <div class="row g-2">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header justify-content-center">
                        <h4 class="mb-0">{{translate('Android')}}</h4>
                    </div>
                    <div class="card-body">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('play_store_config'))
                        <form
                            action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.system-setup.app_setting',['platform' => 'android']):'javascript:'}}"
                            method="post">
                            @csrf
                            <div class="form-group">
                                <div class="form-group d-flex gap-3 align-items-center justify-content-between">
                                    <div
                                        class="text-dark font-weight-bold">{{ translate('Enable_Download_Link_for_Web_Footer') }}</div>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input" name="play_store_status"
                                               value="1" {{(isset($config) && $config['status']==1)?'checked':''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <input type="text" id="play_store_link" name="play_store_link"
                                           value="{{$config['link']??''}}" class="form-control" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="text-dark" for="android_min_version">{{ translate('Minimum_Version_for_Force_Update') }}
                                        <i class="tio-info text-danger" data-toggle="tooltip" data-placement="right"
                                           title="{{ translate("If there is any update available in the admin panel and for that, the previous user app will not work, you can force the customer from here by providing the minimum version for force update. That means if a customer has an app below this version the customers must need to update the app first. If you don't need a force update just insert here zero (0) and ignore it.") }}"></i>
                                    </label>
                                    <input type="number" min="0" step=".1" id="android_min_version"
                                           name="android_min_version"
                                           value="{{$config['min_version']??''}}" class="form-control"
                                           placeholder="{{ translate('EX: 4.0') }}">
                                </div>
                            </div>

                            <div class="btn--container">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        onclick="{{env('APP_MODE')!='demo'?'':'call_demo()'}}"
                                        class="btn btn-primary">{{translate('save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header justify-content-center">
                        <h4 class="mb-0">{{translate('IOS')}}</h4>
                    </div>
                    <div class="card-body">
                        @php($config=\App\CentralLogics\Helpers::get_business_settings('app_store_config'))
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.system-setup.app_setting',['platform' => 'ios']):'javascript:'}}"
                            method="post">
                            @csrf
                            <div class="form-group">
                                <div class="form-group d-flex align-items-center gap-3 justify-content-between">
                                    <div
                                        class="text-dark font-weight-bold">{{ translate('Enable download link for web footer') }}</div>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input" name="app_store_status"
                                               value="1" {{(isset($config) && $config['status']==1)?'checked':''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <input type="text" id="app_store_link" name="app_store_link"
                                           value="{{$config['link']??''}}" class="form-control" placeholder="">
                                </div>

                                <div class="form-group">
                                    <label class="text-dark" for="ios_min_version">{{ translate('Minimum version for force update') }}
                                        <i class="tio-info text-danger" data-toggle="tooltip" data-placement="right"
                                           title="{{ translate("If there is any update available in the admin panel and for that, the previous user app will not work, you can force the customer from here by providing the minimum version for force update. That means if a customer has an app below this version the customers must need to update the app first. If you don't need a force update just insert here zero (0) and ignore it.") }}"></i>
                                    </label>
                                    <input type="number" min="0" step=".1" id="ios_min_version" name="ios_min_version"
                                           value="{{$config['min_version']??''}}" class="form-control"
                                           placeholder="{{ translate('EX: 4.0') }}">
                                </div>
                            </div>

                            <div class="btn--container">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary call-demo">{{translate('save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
