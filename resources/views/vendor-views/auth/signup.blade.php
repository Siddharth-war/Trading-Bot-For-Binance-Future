<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>{{translate('Vendor')}} | {{translate('SignUp')}}</title>

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
            background: #ffffff;
            box-shadow: none !important;
            width: 100%;
        }
        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /* background:  url('https://thumbs.dreamstime.com/b/festive-dessert-illustration-charming-diwali-food-doodle-banner-displaying-delightful-selection-sweets-red-backdrop-345790609.jpg') no-repeat center center/cover; */
            background:  url('https://global.dadus.co.in/cdn/shop/files/Desktop_-_Banner1_2.jpg?v=1696916790&width=2800') no-repeat center center/cover;

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
            background: #ffffff;
        }
        .right-side {
            width: 50%;
            background: #fbdaabc7;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: #fff;
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
                <h5 class="fw-bold">Vendor Panel</h5>
                <p>Sign Up</p>

                <a class="btn btn-primary" href="{{ route('vendor.auth.login') }}">Sign In</a   >

            </div>

        </div>
        <div class="right-side">
            <div class="login-box">
                <h2 class="text-primary">Sign Up</h2>
                <form  action="{{ route('vendor.auth.registration') }}" method="post">
                    @csrf
                    <div class="mb-2">
                        <input type="text" class="form-control " name="first_name" id="signinSrEmail"
                        tabindex="1" placeholder="{{ translate('First Name') }}"
                        aria-label="email@address.com" required
                        data-msg="{{ translate('Please enter first name') }}">
                        @error('first_name')
                        <p class="text-sm text-danger">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <input type="text" class="form-control " name="last_name" id="signinSrEmail"
                        tabindex="1" placeholder="{{ translate('Last Name') }}"
                        aria-label="email@address.com" required
                        data-msg="{{ translate('Please enter last name') }}">
                        @error('last_name')
                        <p class="text-sm text-danger">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="mb-2">
                        <input type="email" class="form-control " name="email" id="signinSrEmail"
                        tabindex="1" placeholder="{{ translate('email@address.com') }}"
                        aria-label="email@address.com" required
                        data-msg="{{ translate('Please enter a valid email address') }}">
                        @error('email')
                        <p class="text-sm text-danger">{{$message}}</p>
                        @enderror
                    </div>
                    <div class="mb-2">
                       <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control form-control-lg"
                                name="password" id="signupSrPassword"
                                placeholder="{{ translate('Please enter password') }}"
                               >
                               @error('password')
                               <p class="text-sm text-danger">{{$message}}</p>
                               @enderror
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="input-group input-group-merge">
                             <input type="password" class="js-toggle-password form-control form-control-lg"
                                 name="confirm_password" id="signupSrPassword"
                                 placeholder="{{ translate('Please enter confirm password') }}"
                           >
                           @error('confirm_password')
                           <p class="text-sm text-danger">{{$message}}</p>
                           @enderror
                         </div>
                     </div>
                     <div class="mb-2">
                        <div class="input-group input-group-merge">
                             <input type="text" class="js-toggle-password form-control form-control-lg"
                                 name="phone" id="signupSrPassword" maxlength="10"
                                 placeholder="{{ translate('Please enter phone') }}"
                           >
                        </div>
                        @error('phone')
                        <p class="text-sm text-danger">{{$message}}</p>
                        @enderror
                     </div>

                    <button type="submit" class="btn btn-primary"
                        id="signInBtn">{{ translate('sign_up') }}</button>

                </form>
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



    </body>
    </html>
