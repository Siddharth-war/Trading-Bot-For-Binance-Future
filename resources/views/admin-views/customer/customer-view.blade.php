@extends('layouts.admin.app')

@section('title', translate('Customer Details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-print-none pb-2">
            <div class="d-flex flex-wrap gap-2 align-items-center mb-3 border-bottom pb-3">
                <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                    <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="">
                    <span class="page-header-title">
                        {{translate('customer_Details')}}
                    </span>
                </h2>
            </div>

            <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center mb-3">
                <div class="d-flex flex-column gap-2">
                    <h2 class="page-header-title h1">{{translate('customer_ID')}} #{{$customer['id']}}</h2>
                    <span class="">
                        <i class="tio-date-range"></i>
                        {{translate('joined_at')}} : {{date('d M Y H:i:s',strtotime($customer['created_at']))}}
                    </span>
                </div>

                <div class="d-flex flex-wrap gap-3 justify-content-lg-end">
                    <a class="btn btn-primary" href="{{ route('admin.customer.customer_transaction',[$customer['id']]) }}">
                        {{translate('point_History')}}
                    </a>
                    <a href="{{route('admin.dashboard')}}" class="btn btn-primary">
                        <i class="tio-home-outlined"></i>
                        {{translate('dashboard')}}
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-2 g-2">


            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="resturant-card bg--2">
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/dashboard/1.png')}}" alt="{{translate('dashboard')}}">
                    <div class="for-card-text font-weight-bold  text-uppercase mb-1">{{translate('wallet')}} {{translate('balance')}}</div>
                    <div class="for-card-count">{{Helpers::set_symbol($customer->wallet_balance??0)}}</div>
                </div>
            </div>


            <div class="col-lg-6 col-md-6 col-sm-6">
                <div class="resturant-card bg--3">
                    <img class="resturant-icon" src="{{asset('/public/assets/admin/img/dashboard/3.png')}}" alt="{{translate('dashboard')}}">
                    <div class="for-card-text font-weight-bold  text-uppercase mb-1">{{translate('loyalty_point')}} {{translate('balance')}}</div>
                    <div class="for-card-count">{{$customer->point??0}}</div>
                </div>
            </div>
        </div>

        <div class="row flex-wrap-reverse g-2" id="printableArea">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row align-items-center">
                            <div class="col-sm-4 col-md-6 col-xl-7">
                                <h5 class="d-flex gap-2 align-items-center">
                                    {{translate('Order List')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $orders->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-xl-5">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="{{translate('Search by order ID')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="py-3">
                        <div class="table-responsive datatable-custom">
                            <table id="columnSearchDatatable"
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100"
                                data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true
                                }'>
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th class="text-center">{{translate('order_ID')}}</th>
                                        <th class="text-center">{{translate('total_Amount')}}</th>
                                        <th class="text-center">{{translate('action')}}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($orders as $key=>$order)
                                    <tr>
                                        <td>{{$orders->firstItem() + $key}}</td>
                                        <td class="table-column-pl-0 text-center">
                                            <a class="text-dark" href="{{route('admin.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                        </td>
                                        <td class="text-center">{{ Helpers::set_symbol($order['order_amount'] + $order['delivery_charge']) }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                    <a class="btn btn-outline-success btn-sm square-btn"
                                                    href="{{route('admin.orders.details',['id'=>$order['id']])}}"><i
                                                            class="tio-visible"></i></a>
                                                    <a class="btn btn-outline-info btn-sm square-btn" target="_blank"
                                                    href="{{route('admin.orders.generate-invoice',[$order['id']])}}"><i
                                                            class="tio-download"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="table-responsive px-3">
                        <div class="d-flex justify-content-lg-end">
                            {!! $orders->links() !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title d-flex gap-2"><span class="tio-user"></span> {{$customer['f_name'].' '.$customer['l_name']}}</h4>
                    </div>

                    @if($customer)
                        <div class="card-body">
                            <div class="media gap-3">
                                <div class="avatar avatar-xl avatar-circle">
                                    <img
                                        class="img-fit rounded-circle"
                                        src="{{$customer->imageFullPath}}"
                                        alt="{{translate('Image Description')}}">
                                </div>
                                <div class="media-body d-flex flex-column gap-1">
                                    <div class="text-dark d-flex gap-2 align-items-center"><span class="tio-email"></span> <a class="text-dark" href="mailto:{{$customer['email']}}">{{$customer['email']}}</a></div>
                                    <div class="text-dark d-flex gap-2 align-items-center"><span class="tio-call-talking-quiet"></span> <a class="text-dark" href="tel:{{$customer['phone']}}">{{$customer['phone']}}</a></div>
                                    <div class="text-dark d-flex gap-2 align-items-center"><span class="tio-shopping-basket-outlined"></span> {{$customer->orders->count()}} {{translate('orders')}}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-header-title d-flex gap-2"><span class="tio-home"></span> {{translate('addresses')}}</h4>
                    </div>

                    @if($customer)
                        <div class="card-body">
                            @foreach($customer->addresses as $address)
                                <ul class="list-unstyled list-unstyled-py-2">
                                    <li>
                                        <i class="tio-city mr-2"></i>
                                        {{$address['address_type']}}
                                    </li>
                                    <li>
                                        <i class="tio-call-talking-quiet mr-2"></i>
                                        {{$address['contact_person_number']}}
                                    </li>
                                    <li class="li-pointer">
                                        <a class="text-muted" target="_blank"
                                           href="http://maps.google.com/maps?z=12&t=m&q=loc:{{$address['latitude']}}+{{$address['longitude']}}">
                                            <i class="tio-map mr-2"></i>
                                            {{$address['address']}}
                                        </a>
                                    </li>
                                </ul>
                                <hr>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade point-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">

                    <h5 class="modal-title h4" id="mySmallModalLabel"> {{translate('add')}} {{translate('point')}} </h5>
                    <button type="button" class="btn btn-xs btn-icon btn-ghost-secondary" data-dismiss="modal"
                            aria-label="Close">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                </div>

                <form action="{{route('admin.customer.AddPoint',[$customer['id']])}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="number" name="point" class="form-control" min="1" max="100000"
                                   placeholder="{{translate('EX')}} : 100" required>
                        </div>
                        <button class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection

@push('script_2')
        <script src="{{asset('public/assets/admin/js/customer-view.js')}}"></script>
@endpush
