<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>{{translate('Customer')}} | {{translate('Login')}}</title>

        @php($icon = \App\Model\BusinessSetting::where(['key' => 'fav_icon'])->first()?->value??'')
        <link rel="shortcut icon" href="">
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/app/public/restaurant/' . $icon ?? '') }}">

        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/vendor.min.css">
        <link rel="stylesheet" href="{{asset('public/assets/admin')}}/vendor/icon-set/style.css">

        <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/theme.minc619.css?v=1.0">
        <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/style.css">
        <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/toastr.css">


    <style>
        .form-control {
            border-radius: 0 !important;
            padding: 12px;
            border: none !important;
            background: #f1f1f1;
            box-shadow: none !important;
            width: 100%;
        }
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url('http://localhost/Admin-panel-new-install/public/assets/admin/svg/components/bg.png') no-repeat center center/cover;
        }
        .login-container {
            width: 55%;
            height: 70vh;
            display: flex;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            overflow: hidden;
        }
        .left-side {
            width: 50%;
            background: #fff;
        }
        .right-side {
            width: 50%;
            background: #d8e4f3;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 350px;
        }
        .login-box h2 {
            font-weight: bold;
            color: #004d00;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 25px;
            padding: 12px;
            border: none;
            background: #f1f1f1;
            box-shadow: inset 3px 3px 6px rgba(0,0,0,0.1);
        }
        .btn-login {
            background: #007b2b;
            color: white;
            border-radius: 25px;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            border: none;
            transition: 0.3s;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-side d-flex justify-content-center align-items-center">
            <div class="text-center text-primary">
                <h1 class="fw-bold">WELCOME</h1>
                <p>Sign in to continue</p>
            </div>
        </div>
        <div class="right-side">
            <div class="login-box">
                <h2 class="text-primary">LOGIN</h2>
                <form  action="{{ route('customer.auth.login') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <input type="email" class="form-control " name="email" id="signinSrEmail"
                        tabindex="1" placeholder="{{ translate('email@address.com') }}"
                        aria-label="email@address.com" required
                        data-msg="{{ translate('Please enter a valid email address') }}">
                    </div>
                    <div class="mb-3">
                       <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control form-control-lg"
                                name="password" id="signupSrPassword"
                                placeholder="{{ translate('8+ characters required') }}"
                                aria-label="8+ characters required" required
                                data-msg="{{ translate('Your password is invalid. Please try again.') }}"
                                data-hs-toggle-password-options='{
                                    "target": "#changePassTarget",
                                    "defaultClass": "tio-hidden-outlined",
                                    "showClass": "tio-visible-outlined",
                                    "classChangeTarget": "#changePassIcon"
                                    }'>
                            <div id="changePassTarget" class="input-group-append">
                                <a class="input-group-text" href="javascript:">
                                    <i id="changePassIcon" class="tio-visible-outlined"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="toggle-container mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="termsCheckbox" name="remember">
                            <label class="form-check-label" for="termsCheckbox">
                                {{ translate('remember_me') }}
                            </label>
                        </div>
                        {{-- <a href="#" class="forgot-password text-primary">FORGOT PASSWORD</a> --}}
                    </div>
                    @php($recaptcha = \App\CentralLogics\Helpers::get_business_settings('recaptcha'))
                    @if (isset($recaptcha) && $recaptcha['status'] == 1)
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                        <input type="hidden" name="set_default_captcha" id="set_default_captcha_value"
                            value="0">

                        <div class="row p-2 d-none" id="reload-captcha">
                            <div class="col-8 pr-0">
                                <input type="text" class="form-control form-control-lg default-captcha-value"
                                    name="default_captcha_value" value=""
                                    placeholder="{{ translate('Enter captcha value') }}" autocomplete="off">
                            </div>
                            <div class="col-4 input-icons bg-white rounded">
                                <a class="re-captcha">
                                    <img src="{{ URL('/admin/auth/code/captcha/1') }}"
                                        class="input-field default-recaptcha" id="default_recaptcha_id">
                                    <i class="tio-refresh icon"></i>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="row p-2">
                            <div class="col-8 pr-0">
                                <input type="text" class="form-control form-control-lg default-captcha-value"
                                    name="default_captcha_value" value=""
                                    placeholder="{{ translate('Enter captcha value') }}" autocomplete="off">
                            </div>
                            <div class="col-4 input-icons bg-white rounded">
                                <a class="re-captcha">
                                    <img src="{{ URL('/admin/auth/code/captcha/1') }}"
                                        class="input-field default-recaptcha" id="default_recaptcha_id">
                                    <i class="tio-refresh icon"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary"
                        id="signInBtn">{{ translate('sign_in') }}</button>

                </form>
                @if(env('APP_MODE')=='demo')
                <div class="border-top border-primary pt-5 mt-10">
                    <div class="row">
                        <div class="col-10">
                            <span>{{translate('Email : admin@admin.com')}}</span><br>
                            <span>{{translate('Password : 12345678')}}</span>
                        </div>
                        <div class="col-2">
                            <button class="btn btn-primary px-3 copy-cred"><i class="tio-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>

    <script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>

    <script src="{{asset('public/assets/admin')}}/js/theme.min.js"></script>
    <script src="{{asset('public/assets/admin')}}/js/toastr.js"></script>
    {!! Toastr::message() !!}

    @if ($errors->any())
        <script>
            "use strict";

            @foreach($errors->all() as $error)
            toastr.error('{{$error}}', Error, {
                CloseButton: true,
                ProgressBar: true
            });
            @endforeach
        </script>
    @endif

    <script>
        "use strict";

        $(document).on('ready', function () {
            $('.js-toggle-password').each(function () {
                new HSTogglePassword(this).init()
            });

            $('.js-validate').each(function () {
                $.HSCore.components.HSValidation.init($(this));
            });

            $(".re-captcha").click(function() {
                re_captcha();
            });

            $(".copy-cred").click(function() {
                copy_cred();
            });
        });
    </script>

    @if(isset($recaptcha) && $recaptcha['status'] == 1)
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script src="https://www.google.com/recaptcha/api.js?render={{$recaptcha['site_key']}}"></script>
            <script>
                "use strict";
                $('#signInBtn').click(function (e) {

                    if( $('#set_default_captcha_value').val() == 1){
                        $('#form-id').submit();
                        return true;
                    }

                    e.preventDefault();

                    if (typeof grecaptcha === 'undefined') {
                        toastr.error('Invalid recaptcha key provided. Please check the recaptcha configuration.');

                        $('#reload-captcha').removeClass('d-none');
                        $('#set_default_captcha_value').val('1');

                        return;
                    }

                    grecaptcha.ready(function () {
                        grecaptcha.execute('{{$recaptcha['site_key']}}', {action: 'submit'}).then(function (token) {
                            document.getElementById('g-recaptcha-response').value = token;
                            document.querySelector('form').submit();
                        });
                    });

                    window.onerror = function(message) {
                        var errorMessage = 'An unexpected error occurred. Please check the recaptcha configuration';
                        if (message.includes('Invalid site key')) {
                            errorMessage = 'Invalid site key provided. Please check the recaptcha configuration.';
                        } else if (message.includes('not loaded in api.js')) {
                            errorMessage = 'reCAPTCHA API could not be loaded. Please check the recaptcha API configuration.';
                        }

                        $('#reload-captcha').removeClass('d-none');
                        $('#set_default_captcha_value').val('1');

                        toastr.error(errorMessage)
                        return true;
                    };
                });
            </script>
    @endif
        <script>
            "use strict";

            function re_captcha() {
                let $url = "{{ URL('/admin/auth/code/captcha') }}";
                $url = $url + "/" + Math.random();
                document.getElementById('default_recaptcha_id').src = $url;
                console.log('url: '+ $url);
            }
        </script>


    @if(env('APP_MODE')=='demo')
        <script>
            "use strict";

            function copy_cred() {
                $('#signinSrEmail').val('admin@admin.com');
                $('#signupSrPassword').val('12345678');
                toastr.success('{{\App\CentralLogics\translate("Copied successfully!")}}', 'Success!', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        </script>
    @endif

    <script>
        if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public/assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
    </script>
    </body>
    </html>
