@extends('layouts.branch.app')

@section('title', translate('Order List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="container-fluid py-5">

        <!-- Page Header -->
        <div class="">
            <div class="row align-items-center mb-3">
                <div class="col-6">
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                        <h2 class="h1 mb-0 d-flex align-items-center gap-1">
                            <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/all_orders.png')}}" alt="">
                            <span class="page-header-title">
                {{translate('Table Orders')}}
                </span>
                        </h2>
                        <span class="badge badge-soft-dark rounded-50 fz-14">{{$orders->total()}}</span>
                    </div>
{{--                    <h2 class="">{{translate('Table Orders')}}<img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/all_orders.png')}}" alt=""> <span class="badge badge-soft-dark ml-2"></span></h2>--}}
                </div>

            </div>
            <div id="all_running_order">
                <div class="card py-4">
                    <div class="row align-items-center mb-3">
                        <div class="col-6"></div>
                        <div class="col-3 ">
                            <!-- Select -->
                            <div id="invoice_btn" class="{{ is_null($table_id) ? 'd-none' : '' }}">
                                <a class="btn btn-sm btn-white float-right" href="{{ route('branch.table.order.running.invoice', ['table_id' => $table_id]) }}"><i class="tio-print"></i> {{translate('invoice')}}</a>
                            </div>
                            <!-- End Select -->
                        </div>
                        <div class="col-3">
                            <!-- Select -->
                            <select class="custom-select custom-select-sm text-capitalize" name="table" id="select_table">
                                <option disabled selected>--- {{translate('select')}} {{translate('table')}} ---</option>
                                @foreach($tables as $table)
                                    <option value="{{$table['id']}}" {{$table_id==$table['id'] ? 'selected' : ''}}>{{translate('Table')}} - {{$table['number']}}</option>
                                @endforeach
                            </select>
                            <!-- End Select -->
                        </div>
                    </div>
{{--                    <div class="card-body p-3">--}}
                        <div class="table-responsive datatable-custom">
                            <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                   style="width: 100%">
                                <thead class="thead-light">
                                <tr>
                                    <th class="">
                                        {{translate('SL')}}
                                    </th>
                                    <th class="table-column-pl-0">{{translate('order')}}</th>
                                    <th>{{translate('date')}}</th>
                                    <th>{{translate('branch')}}</th>
                                    <th>{{translate('table')}}</th>
                                    <th>{{translate('payment')}} {{translate('status')}}</th>
                                    <th>{{translate('total')}}</th>
                                    <th>{{translate('order')}} {{translate('status')}}</th>
                                    <th>{{translate('number of people')}}</th>
                                    <th>{{translate('actions')}}</th>
                                </tr>
                                </thead>

                                <tbody id="set-rows">
                                @foreach($orders as $key=>$order)

                                    <tr class="status-{{$order['order_status']}} class-all">
                                        <td class="">
                                            {{$key+$orders->firstItem()}}
                                        </td>
                                        <td class="table-column-pl-0">
                                            <a href="{{route('branch.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
                                        </td>
                                        <td>{{date('d M Y',strtotime($order['created_at']))}}</td>
                                        <td>
                                            <label class="badge badge-soft-primary">{{$order->branch?$order->branch->name:'Branch deleted!'}}</label>
                                        </td>
                                        <td>
                                            @if($order->table)
                                                <label class="badge badge-soft-info">{{translate('table')}} - {{$order->table->number}}</label>
                                            @else
                                                <label class="badge badge-soft-info">{{translate('table deleted')}}</label>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->payment_status=='paid')
                                                <span class="badge badge-soft-success">
                                        <span class="legend-indicator bg-success"></span>{{translate('paid')}}</span>
                                            @else
                                                <span class="badge badge-soft-danger">
                                        <span class="legend-indicator bg-danger"></span>{{translate('unpaid')}}</span>
                                            @endif
                                        </td>
                                        <td>{{ \App\CentralLogics\Helpers::set_symbol($order['order_amount']) }}</td>
                                        <td class="text-capitalize">
                                            @if($order['order_status']=='pending')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-info"></span>{{translate('pending')}}</span>
                                            @elseif($order['order_status']=='confirmed')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-info"></span>{{translate('confirmed')}}</span>
                                            @elseif($order['order_status']=='cooking')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-info"></span>{{translate('cooking')}}</span>
                                            @elseif($order['order_status']=='done')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-info"></span>{{translate('done')}}</span>
                                            @elseif($order['order_status']=='completed')
                                                <span class="badge badge-soft-info ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-info"></span>{{translate('completed')}}</span>
                                            @elseif($order['order_status']=='processing')
                                                <span class="badge badge-soft-warning ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-warning"></span>{{translate('processing')}}</span>
                                            @elseif($order['order_status']=='out_for_delivery')
                                                <span class="badge badge-soft-warning ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-warning"></span>{{translate('out_for_delivery')}}</span>
                                            @elseif($order['order_status']=='delivered')
                                                <span class="badge badge-soft-success ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-success"></span>{{translate('delivered')}}</span>
                                            @else
                                                <span class="badge badge-soft-danger ml-2 ml-sm-3">
                                        <span class="legend-indicator bg-danger"></span>{{str_replace('_',' ',$order['order_status'])}}</span>
                                            @endif
                                        </td>
                                        <td>{{$order['number_of_people']}}</td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-info square-btn" href="{{route('branch.orders.details',['id'=>$order['id']])}}"><i class="tio-visible"></i></a>
                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                <!-- Pagination -->
                                {!! $orders->links() !!}
                            </div>
                        </div>
                </div>
            </div>
        </div>

        @endsection

@push('script_2')
    <script>
        $(document).on('ready', function () {
            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

           /* $(document).ready(function (){
            $('#select_table').on('change', function (){
                var table = this.value;
                $('#all_running_order').html('');
                $('#order_list').html('');
                $.ajax({
                    url: "{{ url('branch/table/order/running/list') }}",
                    type: "get",
                    data: {
                        table_id : table,
                    },
                    dataType : 'json',
                    success: function (result){
                        $('#order_list').html(result.view);
                    },
                });
            });
        });*/

        $(document).ready(function (){
            $('#select_table').on('change', function (){
                location.href = '{{route('branch.table.order.running')}}' + '?table_id=' + $(this).val();

            });
        });

    </script>
@endpush
