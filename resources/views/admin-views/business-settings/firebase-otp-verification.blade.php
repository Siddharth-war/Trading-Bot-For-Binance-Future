@extends('layouts.admin.app')

@section('title', translate('Firebase OTP Verification'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/third-party.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('third_party')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._3rdparty-inline-menu')

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-end align-items-center">
                            <a class="btn-sm btn btn-outline-primary p-2 cursor-pointer" data-toggle="modal" data-target="#instructionsModal">
                                <i class="tio-info-outined"></i>
                                {{translate('Credentials_Setup')}}
                            </a>
                        </div>
                        <form action="{{route('admin.business-settings.web-app.third-party.firebase-otp-verification-update')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 pt-5">
                                    <?php
                                    $firebaseOTP=\App\CentralLogics\Helpers::get_business_settings('firebase_otp_verification');
                                    ?>
                                    <div class="form-group">
                                        <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control">
                                            <span class="pr-1 d-flex align-items-center switch--label">
                                                <span class="line--limit-1">
                                                    <strong>{{translate('Firebase Auth Verification Status')}}</strong>
                                                    <i class="tio-info-outined"
                                                       data-toggle="tooltip"
                                                       data-placement="top"
                                                       title="{{ translate('If this field is active customers get the OTP through Firebase.') }}">
                                                    </i>
                                                </span>
                                            </span>
                                            <input type="checkbox" class="toggle-switch-input" name="status" {{ isset($firebaseOTP) && $firebaseOTP['status'] == 1 ? 'checked' : '' }}>
                                            <span class="toggle-switch-label text">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label text-capitalize">{{translate('web_api_key')}}</label>
                                        <input type="text" value="{{$firebaseOTP && env('APP_MODE')!='demo' ? $firebaseOTP['web_api_key'] : ''}}" name="web_api_key" class="form-control" placeholder="">
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn-secondary">{{translate('clear')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary call-demo">{{translate('submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="instructionsModal" tabindex="-1" aria-labelledby="instructionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-end">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center my-5">
                        <img src="{{ asset('public/assets/admin/svg/components/instruction.svg') }}">
                    </div>

                    <h5 class="modal-title my-3" id="instructionsModalLabel">{{translate('Instructions')}}</h5>
                    <p>{{ translate('For configuring OTP in the Firebase, you must create a Firebase project first.
                        If you haven’t created any project for your application yet, please create a project first.') }}
                    </p>
                    <p>{{ translate('Now go the') }} <a href="https://console.firebase.google.com/" target="_blank">Firebase console </a>{{ translate('and follow the instructions below') }} -</p>
                    <ol class="d-flex flex-column __gap-5px __instructions">
                        <li>{{ translate('Go to your Firebase project.') }}</li>
                        <li>{{ translate('Navigate to the Build menu from the left sidebar and select Authentication.') }}</li>
                        <li>{{ translate('Get started the project and go to the Sign-in method tab.') }}</li>
                        <li>{{ translate('From the Sign-in providers section, select the Phone option.') }}</li>
                        <li>{{ translate('Ensure to enable the method Phone and press save.') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
