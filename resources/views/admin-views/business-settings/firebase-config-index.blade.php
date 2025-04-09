@extends('layouts.admin.app')

@section('title', translate('Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/firebase.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('system_setup')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._system-settings-inline-menu')

        <div class="row gx-2 gx-lg-3">
            @php($data=\App\CentralLogics\Helpers::get_business_settings('firebase_message_config'))
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.system-setup.firebase_message_config'):'javascript:'}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            @if(isset($data))
                                <div class="form-group">
                                    <label>{{translate('API Key')}}</label>
                                    <input type="text" placeholder="" class="form-control" name="apiKey"
                                        value="{{env('APP_MODE')!='demo'?$data['apiKey']:''}}" required autocomplete="off">
                                </div>

                                <div class="form-group">
                                    <label>{{translate('Auth Domain')}}</label>
                                    <input type="text" class="form-control" name="authDomain" value="{{env('APP_MODE')!='demo'?$data['authDomain']:''}}" required autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Project ID')}}</label>
                                    <input type="text" class="form-control" name="projectId" value="{{env('APP_MODE')!='demo'?$data['projectId']:''}}" required autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label>{{translate('Storage Bucket')}}</label>
                                    <input type="text" class="form-control" name="storageBucket" value="{{env('APP_MODE')!='demo'?$data['storageBucket']:''}}" required autocomplete="off">
                                </div>

                                <div class="form-group">
                                    <label>{{translate('Messaging Sender ID')}}</label>
                                    <input type="text" placeholder="" class="form-control" name="messagingSenderId"
                                        value="{{env('APP_MODE')!='demo'?$data['messagingSenderId']:''}}" required autocomplete="off">
                                </div>

                                <div class="form-group">
                                    <label>{{translate('App ID')}}</label>
                                    <input type="text" placeholder="" class="form-control" name="appId"
                                        value="{{env('APP_MODE')!='demo'?$data['appId']:''}}" required autocomplete="off">
                                </div>

                                <div class="btn--container">
                                    <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                    <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                            class="btn btn-primary call-demo">{{translate('save')}}</button>
                                </div>
                            @else
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">{{translate('configure')}}</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

