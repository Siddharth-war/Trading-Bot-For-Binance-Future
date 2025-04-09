@extends('layouts.admin.app')

@section('title', translate('table order'))

@section('content')
    <div class="container-fluid py-5">
        <div>
            <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                    <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/all_orders.png')}}" alt="">
                    <span class="page-header-title">
                    {{translate('running')}} {{translate('table')}} {{translate('orders')}}
                </span>
                </h2>
                <span class="badge badge-soft-dark rounded-50 fz-14">{{$orders->total()}}</span>
            </div>
        </div>
        <div id="all_running_order">
            <div class="card">
                <div class="card-top px-card pt-4">
                    <div class="row justify-content-between align-items-center gy-2">
                        <div class="col-sm-4 col-md-5 col-lg-4">
                            <div>
                                <form action="" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="Search by ID  customer or payment status" aria-label="Search" value="{{ request()->search }}" required="" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-sm-6 col-md-6 col-lg-7">
                            <div>
                                 <form  class="row" method="get" action="">
                                     <div class="col-md-3">
                                         <div id="invoice_btn" class="{{ is_null($table_id) ? 'd-none' : '' }}">
                                             <a class="form-control btn btn-sm btn-white float-right" href="{{ route('admin.table.order.running.invoice', ['table_id' => $table_id]) }}"><i class="tio-print"></i> {{translate('invoice')}}</a>
                                         </div>
                                     </div>
                                     <div class="col-md-3">
                                         <select class="form-control text-capitalize filter-branch-orders" name="branch">
                                             <option disabled>--- {{translate('select')}} {{translate('branch')}} ---</option>
                                             @foreach(\App\Model\Branch::all() as $branch)
                                                 <option value="{{$branch['id']}}" {{$branchId==$branch['id']?'selected':''}}>{{$branch['name']}}</option>
                                             @endforeach
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <select class="form-control text-capitalize" name="table" id="select_table">
                                             @foreach($tables as $table)
                                                 <option value="{{ $table['id'] }}" {{ $table_id == $table['id'] ? 'selected' : '' }}>{{ translate('Table') }} - {{ $table['number'] }}</option>
                                             @endforeach
                                         </select>
                                     </div>
                                     <div class="col-md-3">
                                         <button type="submit" class="btn btn-primary w-100">filter</button>
                                     </div>
                                 </form>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body mt-4 px-3">
                    <div class="table-responsive datatable-custom">
                        <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table width-100-percent">
                            <thead class="thead-light">
                            <tr>
                                <th>
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
                                    <td>{{$orders->firstitem()+$key}}</td>
                                    <td class="table-column-pl-0">
                                        <a href="{{route('admin.orders.details',['id'=>$order['id']])}}">{{$order['id']}}</a>
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
                                        <div class="dropdown">
                                            <a class="btn btn-sm btn-outline-primary square-btn" href="{{route('admin.orders.details',['id'=>$order['id']])}}">
                                                <i class="tio-invisible"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                        <div class="col-sm-auto">
                            <div class="d-flex justify-content-center justify-content-sm-end">
                                {!! $orders->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('script_2')
    <script>
        $(document).ready(function () {
            function getParameterByName(name, url = window.location.href) {
                name = name.replace(/[\[\]]/g, '\\$&');
                let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
                let results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, ' '));
            }

            // Populate tables on branch change
            $('.filter-branch-orders').change(function () {
                let branchId = this.value;
                $.ajax({
                    url: '{{ url('/admin/table/order/tables-by-branch') }}/' + branchId,
                    type: 'GET',
                    success: function (data) {
                        let tableSelect = $('#select_table');
                        tableSelect.empty();
                        tableSelect.append('<option disabled selected>--- {{translate('select')}} {{translate('table')}} ---</option>');
                        data.forEach(function (table) {
                            tableSelect.append('<option value="' + table.id + '">' + '{{translate('Table')}} - ' + table.number + '</option>');
                        });

                        // Set table from URL if available
                        let tableIdFromUrl = getParameterByName('table');
                        if (tableIdFromUrl) {
                            tableSelect.val(tableIdFromUrl);
                        }
                    }
                });
            });

            // Show table name after selection
            $('#select_table').change(function () {
                let selectedTableText = $("#select_table option:selected").text();
                // Optionally handle the selected table text
            });

            // Initially populate tables for the selected or default branch
            let initialBranchId = $('.filter-branch-orders').val();
            if (initialBranchId) {
                $.ajax({
                    url: '{{ url('/admin/table/order/tables-by-branch') }}/' + initialBranchId,
                    type: 'GET',
                    success: function (data) {
                        let tableSelect = $('#select_table');
                        tableSelect.empty();
                        tableSelect.append('<option disabled selected>--- {{translate('select')}} {{translate('table')}} ---</option>');
                        data.forEach(function (table) {
                            tableSelect.append('<option value="' + table.id + '">' + '{{translate('Table')}} - ' + table.number + '</option>');
                        });

                        // Set table from URL if available
                        let tableIdFromUrl = getParameterByName('table');
                        if (tableIdFromUrl) {
                            tableSelect.val(tableIdFromUrl);
                        }
                    }
                });
            }
        });
    </script>
@endpush
