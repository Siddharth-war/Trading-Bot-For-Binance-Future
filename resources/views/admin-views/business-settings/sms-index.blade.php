@extends('layouts.admin.app')

@section('title', translate('SMS Module Setup'))

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

        <div class="row g-3 mb-2">
            @if($publishedStatus == 1)
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-around">
                            <h4 class="pt-2 text-danger">
                                <i class="tio-info-outined"></i>
                                {{ translate('Your current sms settings are disabled, because you have enabled
                                sms gateway addon, To visit your currently active sms gateway settings please follow
                                the link.') }}
                            </h4>
                            <span>
                               <a href="{{!empty($paymentUrl) ? $paymentUrl : ''}}" class="btn btn-outline-primary"><i class="tio-settings mr-1"></i>{{translate('settings')}}</a>
                            </span>
                        </div>
                    </div>
                </div>
            @endif

                @foreach($dataValues as $gateway)
                    <div class="col-md-6 mb-30 sms-gatway-cards mb-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="page-title">{{translate($gateway->key_name)}}</h4>
                            </div>
                            <div class="card-body p-30">
                                <form action="{{route('admin.business-settings.web-app.sms-module-update',[$gateway->key_name])}}" method="POST"
                                      id="{{$gateway->key_name}}-form" enctype="multipart/form-data">
                                    @csrf
                                    @method('post')
                                    <div class="discount-type">
                                        <div class="d-flex align-items-center gap-4 gap-xl-5 mb-30">
                                            <div class="custom-radio">
                                                <input type="radio" id="{{$gateway->key_name}}-active"
                                                       name="status"
                                                       value="1" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'checked':''}}>
                                                <label for="{{$gateway->key_name}}-active"> {{ translate('Active') }}</label>
                                            </div>
                                            <div class="custom-radio">
                                                <input type="radio" id="{{$gateway->key_name}}-inactive"
                                                       name="status"
                                                       value="0" {{$dataValues->where('key_name',$gateway->key_name)->first()->live_values['status']?'':'checked'}}>
                                                <label for="{{$gateway->key_name}}-inactive"> {{ translate('Inactive') }}</label>
                                            </div>
                                        </div>

                                        <input name="gateway" value="{{$gateway->key_name}}" class="d-none">
                                        <input name="mode" value="live" class="d-none">

                                        @php($skip=['gateway','mode','status'])
                                        @foreach($dataValues->where('key_name',$gateway->key_name)->first()->live_values as $key=>$value)
                                            @if(!in_array($key,$skip))
                                                <div class="form-floating mb-30 mt-30">
                                                    <label for="exampleFormControlInput1" class="form-label">{{translate($key)}} *</label>
                                                    <input type="text" class="form-control mb-3"
                                                           name="{{$key}}"
                                                           placeholder="{{translate($key)}} *"
                                                           value="{{env('APP_ENV')=='demo'?'':$value}}">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary demo_check">
                                            {{ translate('Update') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        @if($publishedStatus == 1)
        $('.sms-gatway-cards').find('input').each(function(){
            $(this).attr('disabled', true);
        });
        $('.sms-gatway-cards').find('button').each(function(){
            $(this).attr('disabled', true);
        });
        @endif
    </script>
@endpush
