@extends('layouts.admin.app')

@section('title', translate('login setup'))

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
                <form id="login-setup-form" action="{{route('admin.business-settings.restaurant.login-setup-update')}}" method="post">
                    @csrf
                    <div class="p-3 border-bottom">
                        <h4 class="mb-0">{{translate('Setup Login Option')}}</h4>
                        <p class="mb-0">{{translate('The option you select customer will have the to option to login')}}</p>
                    </div>

                    <div class="card-body">
                        <div class="login-option mt-2 mb-5">
                            <div class="row">
                                <div class="col-lg-4 col-sm-6 mb-2">
                                    <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                        <label class="text-dark mb-0">{{translate('Manual Login')}}
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('By enabling manual login, customers will get the option to create account and log in using necessary credentials & password in the app & website') }}">
                                            </i>
                                        </label>
                                        <input name="manual_login" type="checkbox"
                                               {{ $loginOptions->manual_login == 1 ? 'checked' : '' }}
                                               id="otp-manual_login">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 mb-2">
                                    <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                        <label class="text-dark mb-0">{{translate('OTP Login')}}
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('With OTP Login, customers can log in using their phone number. while new customers can create accounts instantly.') }}">
                                            </i>
                                        </label>
                                        <input name="otp_login" type="checkbox"
                                               {{ $loginOptions->otp_login == 1 ? 'checked' : '' }}
                                               id="otp-login">
                                    </div>
                                </div>
                                <div class="col-lg-4 col-sm-6 mb-2">
                                    <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                        <label class="text-dark mb-0">{{translate('Social Media Login')}}
                                            <i class="tio-info-outined"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ translate('With Social Login, customers can log in using social media credentials. while new customers can create accounts instantly.') }}">
                                            </i>
                                        </label>
                                        <input name="social_media_login" type="checkbox"
                                               {{ $loginOptions->social_media_login == 1 ? 'checked' : '' }}
                                               id="social-media-login">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="social-media-setup my-5" id="social-media-setup" style="display: none;">
                            <div class="mb-2 social-media-setup-header">
                                <h4 class="">{{translate('Social Media Login Setup')}}</h4>
                            </div>
                            <div class="bg-soft-secondary p-4">
                                <h4>{{ translate('Choose Social Media') }}</h4>
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 mb-2">
                                        <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                            <label class="text-dark mb-0">{{translate('Google')}}
                                                <i class="tio-info-outined"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="{{ translate('Enabling Google Login, customers can log in to the site using their existing Gmail credentials.') }}">
                                                </i>
                                            </label>
                                            <input name="google" type="checkbox"
                                                   {{ $socialMediaLoginOptions->google == 1 ? 'checked' : '' }}
                                                   id="google">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 mb-2">
                                        <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                            <label class="text-dark mb-0">{{translate('Facebook')}}
                                                <i class="tio-info-outined"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="{{ translate('Enabling Facebook Login, customers can log in to the site using their existing Facebook credentials.') }}">
                                                </i>
                                            </label>
                                            <input name="facebook" type="checkbox"
                                                   {{ $socialMediaLoginOptions->facebook == 1 ? 'checked' : '' }}
                                                   id="facebook">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 mb-2">
                                        <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                            <label class="text-dark mb-0">{{translate('Apple')}}
                                                <i class="tio-info-outined"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="{{ translate('Enabling Apple Login, customers can log in to the site using their existing Apple login credentials.') }}">
                                                </i>
                                            </label>
                                            <input name="apple" type="checkbox"
                                                   {{ $socialMediaLoginOptions->apple == 1 ? 'checked' : '' }}
                                                   id="apple">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="otp-verification my-5">
                            <div class="mb-2">
                                <h4 class="mb-0">{{translate('OTP Verification')}}</h4>
                                <a>{{translate('The option you select will need to be verified by the customer')}}</a>
                            </div>
                            <div class="bg-soft-secondary p-4">
                                <div class="row">
                                    <div class="col-lg-4 col-sm-6 mb-2">
                                        <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                            <label class="text-dark mb-0">{{translate('Email Verification')}}
                                                <i class="tio-info-outined"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="{{ translate('If Email verification is on, Customers must verify their email address with an OTP to complete the signup process.') }}">
                                                </i>
                                            </label>
                                            <input name="email_verification" type="checkbox"
                                                   {{ $emailVerification == 1 ? 'checked' : '' }}
                                                   id="email-verification">
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-sm-6 mb-2">
                                        <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                            <label class="text-dark mb-0">{{translate('Phone Number Verification')}}
                                                <i class="tio-info-outined"
                                                   data-toggle="tooltip"
                                                   data-placement="top"
                                                   title="{{ translate('If Phone Number verification is on, Customers must verify their Phone Number with an OTP to complete the signup process.') }}">
                                                </i>
                                            </label>
                                            <input class="" name="phone_verification" type="checkbox"
                                                   {{ $phoneVerification == 1 ? 'checked' : '' }}
                                                   id="phone-verification">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn--container mb-4 mx-4">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary call-demo">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="smsConfigModal" tabindex="-1" role="dialog" aria-labelledby="smsConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div>
                        <img src="{{ asset('public/assets/admin/img/sms-img.png') }}" alt="{{ translate('image') }}">
                    </div>
                    <div class="py-4">
                        <h5 class="modal-title" id="smsConfigModalLabel">{{ translate('Set Up SMS Configuration First') }}</h5>
                    </div>
                    <p>{{ translate('It looks like your SMS configuration is not set up yet. To enable the OTP system, please set up the SMS configuration first.') }}</p>
                </div>
                <div class="text text-center mb-5">
                    <a href="{{route('admin.business-settings.web-app.sms-module')}}" target="_blank" class="btn btn-primary">{{ translate('Go to SMS Configuration') }}</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="socialMediaConfigModal" tabindex="-1" role="dialog" aria-labelledby="socialMediaConfigModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div>
                        <img src="{{ asset('public/assets/admin/img/apple.png') }}" alt="{{ translate('image') }}">
                    </div>
                    <div class="py-4">
                        <h5 class="modal-title" id="socialMediaConfigModalLabel">{{ translate('Set Up Apple Configuration First') }}</h5>
                    </div>
                    <p id="socialMediaConfigModalDescription">{{ translate('It looks like your Apple configuration is not set up yet. To enable the Apple login system, please set up the Apple configuration first.') }}</p>
                </div>
                <div class="text text-center mb-5">
                    <a href="{{route('admin.business-settings.web-app.third-party.social-login')}}" target="_blank" class="btn btn-primary">{{ translate('Go to Apple Configuration') }}</a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>

        $(document).ready(function() {
            toggleSocialMediaSetup();

            $('#social-media-login').change(function() {
                toggleSocialMediaSetup();
            });

            function toggleSocialMediaSetup() {
                if ($('#social-media-login').is(':checked')) {
                    $('#social-media-setup').show();
                } else {
                    $('#social-media-setup').hide();
                }
            }
        });

        $(document).ready(function() {
            $('#otp-login').change(function(event) {
                if ($(this).is(':checked')) {
                    isSmsGatewayActivated(function(isActivated) {
                        if (!isActivated) {
                            event.preventDefault();
                            $('#otp-login').prop('checked', false);
                            $('#smsConfigModal').modal('show');
                        }
                        else {
                            $('#phone-verification').prop('checked', true);
                        }
                    });
                }
            });

            // Function to check if any SMS gateway is activated
            function isSmsGatewayActivated(callback) {
                $.ajax({
                    url: '{{ route('admin.business-settings.restaurant.check-active-sms-gateway') }}',
                    method: 'GET',
                    success: function(response) {
                        callback(response > 0);
                    },
                    error: function() {
                        callback(false);
                    }
                });
            }
        });

        $(document).ready(function() {
            function isSocialMediaActivated(socialMedia, callback) {
                $.ajax({
                    url: '{{ route('admin.business-settings.restaurant.check-active-social-media') }}',
                    method: 'GET',
                    success: function(response) {
                        callback(response[socialMedia] == 1);
                    },
                    error: function() {
                        callback(false);
                    }
                });
            }

            $('#apple').change(function(event) {
                if ($(this).is(':checked')) {
                    isSocialMediaActivated('apple', function(isActivated) {
                        if (!isActivated) {
                            event.preventDefault();
                            $('#apple').prop('checked', false);
                            $('#socialMediaConfigModal').modal('show');
                        }
                    });
                }
            });
        });


        $('#login-setup-form').submit(function(event) {
            let manualLogin = $('#otp-manual_login').prop('checked');
            let otpLogin = $('#otp-login').prop('checked');
            let socialMediaLogin = $('#social-media-login').prop('checked');

            if (!manualLogin && !otpLogin && !socialMediaLogin) {
                event.preventDefault();
                Swal.fire({
                    type: 'warning',
                    title: '{{ translate("No Login Option Selected") }}!',
                    text: '{{ translate("Please select at least one login option.") }}',
                    confirmButtonText: '{{ translate("OK") }}',
                    confirmButtonColor: '#2750E6',
                });
            }
        });

    </script>
@endpush
