@extends('layouts.admin.app')

@section('title', translate('Business Settings'))

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

        <form action="{{route('admin.business-settings.restaurant.order-update')}}" method="post">
            @csrf
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="mb-0">
                        {{translate('Order Settings')}}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php($mov=\App\Model\BusinessSetting::where('key','minimum_order_value')->first()->value)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <label class="input-label">
                                    {{translate('min_Order_value')}} ( {{\App\CentralLogics\Helpers::currency_symbol()}} )
                                </label>
                                <input type="number" min="1" value="{{$mov}}"
                                       name="minimum_order_value" class="form-control" placeholder="{{translate('Ex: 9.43896534')}}"
                                       required>
                            </div>
                        </div>
{{--                        @php($defaultPreparationTime=\App\CentralLogics\Helpers::get_business_settings('default_preparation_time'))--}}
{{--                        <div class="col-md-4 col-sm-6">--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="input-label">{{translate('Food_Preparation_Time')}}--}}
{{--                                    <small class="text-danger">{{translate(' ( in_Minute )')}}</small>--}}
{{--                                </label>--}}
{{--                                <input type="number" value="{{$defaultPreparationTime}}"--}}
{{--                                       name="default_preparation_time" class="form-control"--}}
{{--                                       placeholder="{{ translate('Ex: 40') }}" min="0"--}}
{{--                                       required>--}}
{{--                            </div>--}}
{{--                        </div>--}}
                        <div class="col-md-4 col-sm-6">
                            @php($scheduleOrderSlotDuration=\App\CentralLogics\Helpers::get_business_settings('schedule_order_slot_duration'))
                            <div class="form-group">
                                <label class="input-label text-capitalize" for="schedule_order_slot_duration">{{ translate('Schedule_Order_Slot_Duration_Minute') }}</label>
                                <input type="number" name="schedule_order_slot_duration" class="form-control" id="schedule_order_slot_duration" value="{{$scheduleOrderSlotDuration?$scheduleOrderSlotDuration:0}}" min="1" placeholder="{{translate('Ex: 30')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 mb-4 mt-5">
                            @php($cutleryStatus=\App\CentralLogics\Helpers::get_business_settings('cutlery_status'))
                            <div class="form-control d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <label class="text-dark mb-0">{{translate('cutlery status')}}
                                        <i class="tio-info-outined"
                                           data-toggle="tooltip"
                                           data-placement="top"
                                           title="{{ translate('When this option is enabled, users can select whether user want cutlery or not.') }}">
                                        </i>
                                    </label>
                                </div>
                                <label class="switcher">
                                    <input class="switcher_input" type="checkbox" name="cutlery_status" {{ $cutleryStatus == null || $cutleryStatus == 0? '' : 'checked'}} id="cutlery_status">
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary call-demo">{{translate('submit')}}</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

