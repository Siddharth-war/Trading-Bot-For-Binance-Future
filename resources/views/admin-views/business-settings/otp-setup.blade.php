@extends('layouts.admin.app')

@section('title', translate('OTP setup'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('business_setup')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._business-setup-inline-menu')

            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.business-settings.restaurant.otp-setup-update')}}" method="post">
                            @csrf
                            <div class="row">
                                @php($maximumOTPHit=\App\Model\BusinessSetting::where('key','maximum_otp_hit')->first()?->value)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize">{{translate('maximum_OTP_submit_attempt')}}
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('The maximum OTP hit is a measure of how many times a specific one-time password has been generated and used within a time.') }}">
                                            </i>
                                        </label>
                                        <input type="number" value="{{$maximumOTPHit}}" min="1"
                                               name="maximum_otp_hit" class="form-control" required>
                                    </div>
                                </div>

                                @php($OTPResendTime=\App\Model\BusinessSetting::where('key','otp_resend_time')->first()?->value)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize">{{translate('otp_resend_time')}}
                                            <span class="text-danger">( {{ translate('in second') }} )</span>
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('If the user fails to get the OTP within a certain time, user can request a resend.') }}">
                                            </i>
                                        </label>
                                        <input type="number" value="{{$OTPResendTime}}" min="1"
                                               name="otp_resend_time" class="form-control" required>
                                    </div>
                                </div>

                                @php($temporaryBlockTime=\App\Model\BusinessSetting::where('key','temporary_block_time')->first()?->value)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize">{{translate('temporary_block_time')}}
                                            <span class="text-danger">( {{ translate('in second') }} )</span>
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('Temporary OTP block time refers to a security measure implemented by systems to restrict access to OTP service for a specified period of time for wrong OTP submission.') }}">
                                            </i>
                                        </label>
                                        <input type="number" value="{{$temporaryBlockTime}}" min="1"
                                               name="temporary_block_time" class="form-control" required>
                                    </div>
                                </div>

                                @php($maximumLoginHit=\App\Model\BusinessSetting::where('key','maximum_login_hit')->first()?->value)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize">{{translate('maximum_login_attempt')}}
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('The maximum login hit is a measure of how many times a user can submit password within a time.') }}">
                                            </i>
                                        </label>
                                        <input type="number" value="{{$maximumLoginHit}}" min="1"
                                               name="maximum_login_hit" class="form-control" required>
                                    </div>
                                </div>

                                @php($temporaryLoginBlockTime=\App\Model\BusinessSetting::where('key','temporary_login_block_time')->first()?->value)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize">{{translate('temporary_login_block_time')}}
                                            <span class="text-danger">( {{ translate('in second') }} )</span>
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('Temporary login block time refers to a security measure implemented by systems to restrict access for a specified period of time for wrong Password submission.') }}">
                                            </i>
                                        </label>
                                        <input type="number" value="{{$temporaryLoginBlockTime}}" min="1"
                                               name="temporary_login_block_time" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="btn--container mt-4">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary call-demo">{{translate('submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
@endsection
