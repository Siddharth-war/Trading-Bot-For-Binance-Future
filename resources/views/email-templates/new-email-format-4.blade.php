<!DOCTYPE html>
<?php
$lang = \App\CentralLogics\Helpers::get_default_language();
//    $site_direction = \App\CentralLogics\Helpers::system_default_direction();
?>
<html lang="{{ $lang }}" class="">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Email_Template') }}</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;0,500;0,700;1,400&display=swap');

        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            font-size: 13px;
            line-height: 21px;
            color: #737883;
            background: #e9ecef;
            padding: 0;
            display: flex;align-items: center;justify-content: center;
            min-height: 100vh;
        }
        h1,h2,h3,h4,h5,h6 {
            color: #334257;
        }
        * {
            box-sizing: border-box
        }

        :root {
           --base: #ffa726
        }

        .main-table {
            width: 500px;
            background: #FFFFFF;
            margin: 0 auto;
            padding: 40px;
        }
        .main-table-td {
        }
        img {
            max-width: 100%;
        }
        .cmn-btn{
            background: var(--base);
            color: #fff;
            padding: 8px 20px;
            display: inline-block;
            text-decoration: none;
        }
        .mb-1 {
            margin-bottom: 5px;
        }
        .mb-2 {
            margin-bottom: 10px;
        }
        .mb-3 {
            margin-bottom: 15px;
        }
        .mb-4 {
            margin-bottom: 20px;
        }
        .mb-5 {
            margin-bottom: 25px;
        }
        hr {
            border-color : rgba(0, 170, 109, 0.3);
            margin: 16px 0
        }
        .border-top {
            border-top: 1px solid rgba(0, 170, 109, 0.3);
            padding: 15px 0 10px;
            display: block;
        }
        .d-block {
            display: block;
        }
        .privacy {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
        }
        .privacy a {
            text-decoration: none;
            color: #334257;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }
        .privacy a span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #334257;
            display: inline-block;
            margin: 0 7px;
        }
        .social {
            margin: 15px 0 8px;
            display: block;
        }
        .copyright{
            text-align: center;
            display: block;
        }
        div {
            display: block;
        }
        .text-center {
            text-align: center;
        }
        .w-100 {
            width: 100%;
        }
        a {
            text-decoration: none;
        }
        .text-base {
            color: var(--base);
            font-weight: 700
        }
        .mail-img-1 {
            width: 140px;
            height: 60px;
            object-fit: contain
        }
        .mail-img-2 {
            width: 130px;
            height: 45px;
            object-fit: contain
        }
        .mail-img-3 {
            width: 100%;
            height: 172px;
            object-fit: cover
        }
        .social img {
        width: 24px;
        }
    </style>

</head>


<body style="background-color: #e9ecef;padding:15px">

    <table  dir="" class="main-table">
        <tbody>
            <tr>
                <td class="main-table-td">
                    <div class="text-center">
                    <img onerror="this.src='{{ asset('/public/assets/admin/img/blank3.png') }}'" class="mail-img-2"
                    src="{{ asset('storage/app/public/email_template/') }}/{{ $data['icon']??'' }}" id="iconViewer" alt="">
                        <h2 id="mail-title" class="mt-2">{{ $title?? translate('Main_Title_or_Subject_of_the_Mail') }}</h2>
                        <div class="mb-1" id="mail-body">{!! $body?? translate('Hi_Sabrina,') !!}</div>
                        <h2 style="font-size: 26px;margin: 0;letter-spacing:4px">
                            {{ $code??'' }}
                        </h2>
                    </div>
                    @if ($data?->button_url)
                    <span class="d-block text-center" style="margin-top: 16px">
                    <a href="{{ $data['button_url']??'#' }}" class="cmn-btn" id="mail-button">{{ $data['button_name']??'Submit' }}</a>
                    </span>
                    @endif
                    <hr>
                    <div class="mb-2" id="mail-footer">
                        {{ $footer_text ?? translate('Please_contact_us_for_any_queries,_we’re_always_happy_to_help.') }}
                    </div>
                    <div>
                        {{ translate('Thanks_&_Regards') }},
                    </div>
                    <div class="mb-4">
                        {{ $company_name }}
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="privacy">
                    @if(isset($data['privacy']) && $data['privacy'] == 1)
                            <a href="{{ route('privacy-policy') }}" id="privacy-check">{{ translate('Privacy_Policy')}}</a>
                        @endif
                        @if(isset($data['contact']) && $data['contact'] == 1)
                            <a href="{{ route('about-us') }}" id="contact-check">{{ translate('About_Us')}}</a>
                        @endif
                </span>
                    {{--                <span class="social" style="text-align:center">--}}
                    {{--                    @php($social_media = \App\Model\SocialMedia::active()->get())--}}
                    {{--                    @dd($social_media, $data)--}}
                    {{--                    @if (isset($social_media))--}}
                    {{--                        @foreach ($social_media as $social)--}}
                    {{--                            <a href="{{ $social->link }}" target=”_blank” id="{{ $social->name  }}-check" style="margin: 0 5px;text-decoration:none;{{ (isset($data[$social->name]) && $data[$social->name] == 1)?'':'display:none;' }}">--}}
                    {{--                                <img src="{{asset('/public/assets/admin/img/img/')}}/{{ $social->name }}.png" alt="">--}}
                    {{--                            </a>--}}
                    {{--                        @endforeach--}}
                    {{--                    @endif--}}
                    {{--                </span>--}}
                    <span class="copyright" id="mail-copyright">
                        {{ $copyright_text?? translate('Copyright_2023_eFood._All_right_reserved') }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>


</body>
</html>
