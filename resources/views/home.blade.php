@extends('layouts.blank')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-3">
                <div class="card mt-3">
                    <div class="card-body text-center">
                        @php($restaurantLogo=\App\Model\BusinessSetting::where(['key'=>'logo'])->first()->value)
                        <img class="" style="width: 200px!important"
                             onerror="this.src='{{asset('public/assets/admin/img/160x160/img2.jpg')}}'"
                             src="{{asset('storage/app/public/restaurant/'.$restaurantLogo)}}"
                             alt="Logo">
                        <br><hr>

                        <a class="btn btn-primary" href="{{route('admin.dashboard')}}">{{ translate('Dashboard') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
