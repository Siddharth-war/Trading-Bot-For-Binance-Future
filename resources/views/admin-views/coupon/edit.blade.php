@extends('layouts.admin.app')

@section('title', translate('Update Coupon'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/coupon.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Coupon_Update')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <form action="{{route('admin.coupon.update',[$coupon['id']])}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('title')}}</label>
                                        <input type="text" name="title" value="{{$coupon['title']}}" class="form-control"
                                            placeholder="{{ translate('New coupon') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('coupon')}} {{translate('type')}}</label>
                                        <select name="coupon_type" class="form-control" id="coupon_type">
                                            <option value="default" {{$coupon['coupon_type']=='default'?'selected':''}}>
                                                {{translate('default')}}
                                            </option>
                                            <option value="first_order" {{$coupon['coupon_type']=='first_order'?'selected':''}}>
                                                {{translate('first_Order')}}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6" id="limit-for-user" style="display: {{$coupon['coupon_type']=='first_order'?'none':'block'}}">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('limit_For_Same_User')}}</label>
                                        <input type="number" name="limit" value="{{$coupon['limit']}}" class="form-control"
                                            placeholder="{{ translate('EX: 10') }}">
                                    </div>
                                </div>

                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('code')}}</label>
                                        <input type="text" name="code" class="form-control" value="{{$coupon['code']}}"
                                            placeholder="{{Str::random(8)}}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="">{{translate('start_Date')}}</label>
                                        <input type="text" name="start_date" class="js-flatpickr form-control flatpickr-custom" placeholder="{{ translate('Select dates') }}" value="{{date('Y/m/d',strtotime($coupon['start_date']))}}"
                                            data-hs-flatpickr-options='{ "dateFormat": "Y/m/d", "minDate": "today" }'>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label" for="">{{translate('expire_Date')}}</label>
                                        <input type="text" name="expire_date" class="js-flatpickr form-control flatpickr-custom" placeholder="{{ translate('Select dates') }}" value="{{date('Y/m/d',strtotime($coupon['expire_date']))}}"
                                            data-hs-flatpickr-options='{
                                            "dateFormat": "Y/m/d",
                                            "minDate": "today"
                                        }'>
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('min_Purchase')}}</label>
                                        <input type="number" name="min_purchase" step="any" value="{{$coupon['min_purchase']}}"
                                            min="0" max="100000" class="form-control"
                                            placeholder="{{ translate('100') }}">
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-6" id="max_discount_div" style="@if($coupon['discount_type']=='amount') display: none; @endif">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('max_Discount')}}</label>
                                        <input type="number" min="0" max="1000000" step="any"
                                            value="{{$coupon['max_discount']}}" name="max_discount" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('discount_Type')}}</label>
                                        <select name="discount_type" id="discount_type" class="form-control">
                                            <option value="percent" {{$coupon['discount_type']=='percent'?'selected':''}}>{{translate('percent')}}</option>
                                            <option value="amount" {{$coupon['discount_type']=='amount'?'selected':''}}>{{translate('amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('discount_Amount')}}</label>
                                        <input type="number" min="1" max="10000" step="any" value="{{$coupon['discount']}}"
                                            name="discount" class="form-control" placeholder="{{translate('Ex: 500')}}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/coupon.js')}}"></script>
@endpush
