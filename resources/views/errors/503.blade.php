<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ translate('Maintenance Mode') }}</title>
    @php($icon = \App\Model\BusinessSetting::where(['key' => 'fav_icon'])->first()->value??'')
    <link rel="shortcut icon" href="">
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/app/public/restaurant/' . $icon ?? '') }}">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/vendor.min.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/vendor/icon-set/style.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/theme.minc619.css?v=1.0">
    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/style.css?v=1.0">
</head>
<body>

<div class="container">
    <div class="text-center my-5">
        <img class="mt-5" src="{{ asset('public/assets/admin/img/img/maintenance.png') }}" alt="{{ translate('maintenance') }}">
    </div>
    <div class="text-center my-5">
        <h2>{{$exception->getHeaders()['maintenanceMessage'] ?? '' }}</h2>
        <p>{{ $exception->getHeaders()['messageBody'] ?? '' }}</p>
    </div>
    <div class="text-center my-5">
        @if($exception->getHeaders()['businessNumber'] || $exception->getHeaders()['businessEmail'] )
            <h6>{{ translate('Any query? Feel free to call or mail Us') }}</h6>
            @if($exception->getHeaders()['businessNumber'])
                <div>
                    <a href="tel:{{\App\CentralLogics\Helpers::get_business_settings('phone')}}">{{ \App\CentralLogics\Helpers::get_business_settings('phone') }}</a>
                </div>
            @endif
            @if($exception->getHeaders()['businessEmail'])
                <div>
                    <a href="mailto:{{\App\CentralLogics\Helpers::get_business_settings('email_address')}}">{{ \App\CentralLogics\Helpers::get_business_settings('email_address') }}</a>
                </div>
            @endif
        @endif
    </div>
</div>


</body>
</html>
