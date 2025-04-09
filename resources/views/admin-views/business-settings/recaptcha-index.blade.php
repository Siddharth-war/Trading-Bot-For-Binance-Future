@extends('layouts.admin.app')

@section('title', translate('reCaptcha Setup'))

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

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <h3 class="mb-3">{{translate('Google Recapcha Information')}}</h3>
                    <a class="btn-sm btn btn-outline-primary p-2 cursor-pointer" data-toggle="modal" data-target="#instructionsModal">
                        <i class="tio-info-outined"></i>
                        {{translate('Credentials_Setup')}}
                    </a>
                </div>

                <div class="badge-soft-secondary rounded my-5 p-3">
                    <h5 class="m-0">{{ translate('V3 Version is available now. Must setup for ReCAPTCHA V3') }}</h5>
                    <p class="m-0">{{ translate('You must setup for V3 version. Otherwise the default reCAPTCHA will be displayed automatically') }}</p>
                </div>

                <div class="mt-4">
                    @php($config=\App\CentralLogics\Helpers::get_business_settings('recaptcha'))
                    <form
                        action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.third-party.recaptcha_update',['recaptcha']):'javascript:'}}"
                        method="post">
                        @csrf
                        <label class="form-label text-capitalize mb-2">{{translate('status')}}</label>
                        <div class="d-flex flex-wrap mb-4 pl-1">
                            <label class="form-check form--check mr-2 mr-md-4">
                                <input class="form-check-input" type="radio" name="status" id="captcha_active" value="1" {{isset($config) && $config['status']==1?'checked':''}}>
                                <span class="mb-0">{{translate('active')}}</span>
                            </label>
                            <label class="form-check form--check">
                                <input class="form-check-input" type="radio" name="status" id="captcha_inactive" value="0" {{isset($config) && $config['status']==0?'checked':''}}>
                                <span class="mb-0">{{translate('inactive')}} </span>
                            </label>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="text-capitalize">{{translate('Site Key')}}</label><br>
                                    <input type="text" class="form-control" name="site_key" value="{{env('APP_MODE')!='demo'?$config['site_key']??"":''}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-capitalize">{{translate('Secret Key')}}</label><br>
                                    <input type="text" class="form-control" name="secret_key" value="{{env('APP_MODE')!='demo'?$config['secret_key']??"":''}}">
                                </div>
                            </div>
                        </div>

                        <div class="btn--container">
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary call-demo">{{translate('save')}}</button>
                        </div>
                    </form>
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

                    <ol class="d-flex flex-column __gap-5px __instructions">
                        <li>{{translate('To get site key and secret key go to the Credentials page')}}
                            ({{translate('Click')}} <a
                                href="https://www.google.com/recaptcha/admin/create"
                                target="_blank">{{translate('here')}}</a>)
                        </li>
                        <li>{{translate('Add a ')}}
                            <b>{{translate('label')}}</b> {{translate('(Ex: Test Label)')}}
                        </li>
                        <li>
                            {{translate('Select reCAPTCHA v3 as ')}}
                            <b>{{translate('reCAPTCHA Type')}}</b>
                        </li>
                        <li>
                            {{translate('Add')}}
                            <b>{{translate('domain')}}</b>
                            {{translate('(For ex: demo.6amtech.com)')}}
                        </li>
                        <li>
                            {{translate('Check in ')}}
                            <b>"{{translate('Accept the reCAPTCHA Terms of Service')}}"</b>
                        </li>
                        <li>
                            {{translate('Press')}}
                            <b>{{translate('Submit')}}</b>
                        </li>
                        <li>{{translate('Copy')}} <b>Site
                                Key</b> {{translate('and')}} <b>Secret
                                Key</b>, {{translate('paste in the input filed below and')}}
                            <b>Save</b>.
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

@endsection
