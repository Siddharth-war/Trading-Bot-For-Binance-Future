@extends('layouts.branch.app')

@section('title', translate('verify_offline_payments'))

@section('content')
    <div class="content container-fluid">
        <div class="">
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                <h2 class="h1 mb-0 d-flex align-items-center gap-1">
                    <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/all_orders.png')}}" alt="">
                    <span class="page-header-title">
                    {{translate('verify_offline_payments')}}
                </span>
                </h2>
                <span class="badge badge-soft-dark rounded-50 fz-14">{{ $orders->total() }}</span>
            </div>
            <ul class="nav nav-tabs border-0 my-2">
                <li class="nav-item">
                    <a class="nav-link {{Request::is('branch/verify-offline-payment/pending')?'active':''}}"  href="{{route('branch.verify-offline-payment', ['pending'])}}">{{ translate('Pending Orders') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{Request::is('branch/verify-offline-payment/denied')?'active':''}}"  href="{{route('branch.verify-offline-payment', ['denied'])}}">{{ translate('Denied Orders') }}</a>
                </li>
            </ul>
        </div>
        <div class="card">

            <div class="card-top px-card pt-4">
                <div class="row justify-content-between align-items-center gy-2">
                    <div class="col-sm-8 col-md-6 col-lg-4">
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search"
                                       class="form-control"
                                       placeholder="{{translate('Search by Order ID, Order Status or Transaction Reference')}}" aria-label="Search"
                                       value="{{$search}}" required autocomplete="off">
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
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('Order_ID')}}</th>
                            <th>{{translate('Delivery_Date')}}</th>
                            <th>{{translate('Customer_Info')}}</th>
                            <th>{{translate('Total_Amount')}}</th>
                            <th>{{translate('Payment_method')}}</th>
                            <th>{{translate('Verification_status')}}</th>
                            <th class="text-center">{{translate('actions')}}</th>
                        </tr>
                        </thead>

                        <tbody id="set-rows">
                        @foreach($orders as $key=>$order)
                            <tr class="status-{{$order['order_status']}} class-all">
                                <td>{{$orders->firstitem()+$key}}</td>
                                <td>
                                    <a class="text-dark" href="{{route('branch.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                </td>
                                <td>
                                    <div>{{date('d M Y',strtotime($order['delivery_date']))}}</div>
                                    <div>{{date('h:i A',strtotime($order['delivery_time']))}}</div>
                                </td>
                                <td>
                                    @if($order->is_guest == 0)
                                        @if($order->customer)
                                            <h6 class="text-capitalize mb-1">
                                                <a class="text-dark" href="#">{{$order->customer['f_name'].' '.$order->customer['l_name']}}</a>
                                            </h6>
                                            <a class="text-dark fz-12" href="tel:{{$order->customer->phone}}">{{$order->customer->phone}}</a>
                                        @else
                                            <span class="text-capitalize text-muted">
                                            {{translate('Customer_Unavailable')}}
                                            </span>
                                        @endif
                                    @else
                                        <h6 class="text-capitalize text-info">
                                            {{translate('Guest Customer')}}
                                        </h6>
                                    @endif
                                </td>

                                <td>
                                    <div>{{ \App\CentralLogics\Helpers::set_symbol($order['order_amount'] + $order['delivery_charge']) }}</div>
                                </td>

                                <td>
                                        <?php
                                        $payment_info = json_decode($order->offline_payment?->payment_info, true);
                                        ?>
                                    {{ $payment_info['payment_name'] }}
                                </td>
                                <td class="text-capitalize">
                                    @if($order->offline_payment?->status == 0)
                                        <span class="badge badge-soft-info">
                                            {{translate('pending')}}
                                        </span>
                                    @elseif($order->offline_payment?->status == 2)
                                        <span class="badge badge-soft-danger">
                                            {{translate('denied')}}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn--container justify-content-center">
                                        <button class="btn btn-primary offline-payment-details" type="button" id="offline_details"
{{--                                                onclick="get_offline_payment(this)" --}}
                                                data-id="{{ $order['id'] }}"
                                                data-target="" data-toggle="modal">
                                            {{ translate('Verify_Payment') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-lg-end">
                    {!!$orders->links()!!}
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="quick-view" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered coupon-details modal-lg" role="document">
            <div class="modal-content" id="quick-view-modal">
            </div>
        </div>
    </div>
@endsection

@push('script_2')

    <script>
        "use strict";

        $('.offline-payment-details').on('click', function (){
            let id = $(this).data('id')

            $.ajax({
                type: 'GET',
                url: '{{route('branch.offline-modal-view')}}',
                data: {
                    id: id
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#loading').hide();
                    $('#quick-view').modal('show');
                    $('#quick-view-modal').empty().html(data.view);
                }
            });
        })

        function verify_offline_payment(order_id, status) {
            $.ajax({
                type: "GET",
                url: '{{url('/')}}/branch/orders/verify-offline-payment/'+ order_id+ '/' + status,
                success: function (data) {
                    location.reload();
                    if(data.status == true) {
                        toastr.success('{{ translate("offline payment verify status changed") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else{
                        toastr.error('{{ translate("offline payment verify status not changed") }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }

                },
                error: function () {
                }
            });
        }
    </script>

@endpush
