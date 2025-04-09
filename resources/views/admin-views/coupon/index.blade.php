@extends('layouts.admin.app')

@section('title', translate('Add new coupon'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/coupon.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Add_New_Coupon')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <form action="{{route('admin.coupon.store')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('coupon')}} {{translate('type')}}</label>
                                        <select name="coupon_type" class="custom-select" id="coupon_type">
                                            <option value="default">{{translate('default')}}</option>
                                            <option value="first_order">{{translate('first order')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('Coupon_Title')}}</label>
                                        <input type="text" name="title" class="form-control" placeholder="{{ translate('New coupon') }}" required maxlength="100">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <div class="d-flex justify-content-between">
                                            <label class="input-label">{{translate('Coupon_Code')}}</label>
                                            <a href="javascript:void(0)" class="float-right c1 fz-12 generate-code">{{translate('generate_code')}}</a>
                                        </div>
                                        <input type="text" name="code" id="coupon-code" class="form-control" maxlength="15" placeholder="{{Str::random(8)}}" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6" id="limit-for-user">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('limit')}} {{translate('for')}} {{translate('same')}} {{translate('user')}}</label>
                                        <input type="number" name="limit" id="user-limit" class="form-control" placeholder="{{ translate('EX: 10') }}" required min="1">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('discount_Type')}}</label>
                                        <select name="discount_type" id="discount_type" class="form-control">
                                            <option value="percent">{{translate('percent')}}</option>
                                            <option value="amount">{{translate('amount')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize" id="discount_label">{{translate('discount_percent')}}</label>
                                        <input type="number" step="any" min="1" max="10000" placeholder="{{translate('Ex: 50%')}}" id="discount_input" name="discount" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('minimum')}} {{translate('purchase')}}</label>
                                        <input type="number" step="any" name="min_purchase" value="0" min="0" max="100000" class="form-control"
                                            placeholder="{{ translate('100') }}">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6" id="max_discount_div">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('maximum')}} {{translate('discount')}}</label>
                                        <input type="number" step="any" min="0" value="0" max="1000000" name="max_discount" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('start')}} {{translate('date')}}</label>
                                        <input type="text" name="start_date" class="js-flatpickr form-control flatpickr-custom" placeholder="yyyy-mm-dd" data-hs-flatpickr-options='{ "dateFormat": "Y/m/d", "minDate": "today" }'>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('expire')}} {{translate('date')}}</label>
                                        <input type="text" name="expire_date" class="js-flatpickr form-control flatpickr-custom" placeholder="yyyy-mm-dd" data-hs-flatpickr-options='{ "dateFormat": "Y/m/d", "minDate": "today" }'>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex align-items-center gap-2 mb-0">
                                    {{translate('Coupon_Table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $coupons->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <form action="{{url()->current()}}" class="mb-0" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by Title')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                {{translate('Search')}}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('Coupon')}}</th>
                                        <th>{{translate('Amount')}}</th>
                                        <th>{{translate('Coupon_Type')}}</th>
                                        <th>{{translate('duration')}}</th>
                                        <th>{{translate('status')}}</th>
                                        <th class="text-center">{{translate('action')}}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($coupons as $key=>$coupon)
                                    <tr>
                                        <td>{{$coupons->firstItem()+$key}}</td>
                                        <td>
                                            <div>
                                                <div class="fz-14"><strong>{{translate('code')}}: {{$coupon['code']}}</strong></div>
                                                <div class="max-w300 text-wrap fz-12 mt-1">{{$coupon['title']}}</div>
                                            </div>
                                        </td>
                                        @if($coupon->discount_type == 'amount')
                                            <td>{{Helpers::set_symbol($coupon->discount)}}</td>
                                        @else
                                            <td>{{$coupon->discount}}%</td>
                                        @endif
                                        <td>{{translate($coupon->discount_type)}}</td>
                                        <td><div class="text-muted">{{date('d M, Y', strtotime($coupon['start_date']))}} - {{date('d M, Y', strtotime($coupon['expire_date']))}}</div></td>
                                        <td>
                                            <label class="switcher">
                                                <input id="{{$coupon['id']}}" class="switcher_input status-change" {{$coupon['status']==1? 'checked': '' }} type="checkbox"
                                                    data-url="{{route('admin.coupon.status',[$coupon['id'],1])}}"
                                                >
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-primary btn-sm square-btn openBtn" id="{{$coupon['id']}}">
                                                    <i class="tio-invisible"></i>
                                                </a>

                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                href="{{route('admin.coupon.update',[$coupon['id']])}}"><i class="tio-edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                data-id="coupon-{{$coupon['id']}}" data-message="{{translate('Want to delete this coupon ?')}}"><i class="tio-delete"></i></button>
                                            </div>
                                            <form action="{{route('admin.coupon.delete',[$coupon['id']])}}"
                                                method="post" id="coupon-{{$coupon['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $coupons->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="couponDetails" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered coupon-details" role="document">
            <div class="modal-content overflow-hidden">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <i class="tio-clear"></i>
                </button>
                <div class="coupon__details">

                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/coupon.js')}}"></script>

    <script>
        "use strict";

        $('#coupon_type').on('click', function (){
            let type = $(this).val();
            if(type === 'first_order'){
                $('#user-limit').removeAttr('required');
            }
        })

        function generateCode() {
            $.get('{{route('admin.coupon.generate-coupon-code')}}', function (data) {
                $('#coupon-code').val(data)
            });
        }

        function modalShow(t) {
            let couponId = t.id;
            let targetUrl = "{{route('admin.coupon.coupon-details')}}" + "?id=" + couponId;
            $.ajax({
                url: targetUrl,
                type: 'GET',
                dataType: 'json',
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('.coupon__details').html(data.view);
                    $('#couponDetails').modal('show');
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }
    </script>
@endpush
