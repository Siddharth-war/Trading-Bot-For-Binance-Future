@extends('layouts.admin.app')

@section('title', translate('Customer List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('customers')}}
                </span>
            </h2>
            <span class="badge badge-soft-dark rounded-50 fz-14">{{ $customers->total() }}</span>
        </div>

        <div class="card">
            <div class="card-top px-card pt-4">
                <div class="d-flex flex-column flex-md-row flex-wrap gap-3 justify-content-md-between align-items-md-center">
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                class="form-control"
                                placeholder="{{translate('Search_By_Name_or_Phone_or_Email')}}" aria-label="Search"
                                value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div>
                        <button type="button" class="btn btn-outline-primary text-nowrap" data-toggle="dropdown" aria-expanded="false">
                            <i class="tio-download-to"></i>
                            Export
                            <i class="tio-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a type="submit" class="dropdown-item d-flex align-items-center gap-2" href="{{route('admin.customer.excel_import')}}">
                                    <img width="14" src="{{asset('public/assets/admin/img/icons/excel.png')}}" alt="">
                                    {{ translate('Excel') }}
                                </a>
                            </li>
                        </ul>


                        <a href="{{ route('admin.customer.customer_add')}}"  class="btn btn-primary">Add Customer</a>
                    </div>
                </div>
            </div>

            <div class="py-3">
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('customer_Name')}}</th>
                                <th>{{translate('Customer_Info')}}</th>
                                <th>{{translate('total_Orders')}}</th>
                                <th>{{translate('total_Order_Amount')}}</th>
                                <th>{{translate('available_Points')}}</th>
                                <th>{{translate('status')}}</th>
                                <th class="text-center">{{translate('actions')}}</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                        @foreach($customers as $key=>$customer)
                            <tr class="">
                                <td class="">
                                    {{$customers->firstitem()+$key}}
                                </td>
                                <td class="max-w300">
                                    <a class="text-dark media align-items-center gap-2" href="{{route('admin.customer.view',[$customer['id']])}}">
                                        <div class="avatar">
                                            <img src="{{$customer->imageFullPath}}" class="rounded-circle img-fit" alt="">
                                        </div>
                                        <div class="media-body text-truncate">{{$customer['f_name']." ".$customer['l_name']}}</div>
                                    </a>
                                </td>
                                <td>
                                    <div><a href="mailto:{{$customer['email']}}" class="text-dark"><strong>{{$customer['email']}}</strong></a></div>
                                    <div><a class="text-dark" href="tel:{{$customer['phone']}}">{{$customer['phone']}}</a></div>
                                </td>
                                <td>
                                    <label class="badge badge-soft-info py-1 px-5 mb-0">
                                        {{$customer->orders->count()}}
                                    </label>
                                </td>
                                <td>{{$customer->orders->sum('order_amount')}}</td>
                                <td class="show-point-{{$customer['id']}}-table">
                                    {{$customer['point']}}
                                </td>
                                <td>
                                    <label class="switcher">
                                        <input id="{{$customer['id']}}" data-url="{{route('admin.customer.update_status', ['id' => $customer['id']])}}" type="checkbox" class="switcher_input status-change" {{$customer->is_active == 1? 'checked' : ''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-success btn-sm square-btn"
                                           href="{{route('admin.customer.view',[$customer['id']])}}">
                                            <i class="tio-visible"></i>
                                        </a>

                                        <a class="btn btn-outline-success btn-sm square-btn"
                                           href="{{route('admin.customer.customer_edit',[$customer['id']])}}">
                                            <i class="tio-edit"></i>
                                        </a>

                                        <button class="btn btn-outline-danger btn-sm square-btn form-alert"  data-id="customer-{{$customer['id']}}" data-message="{{translate('delete_this_user')}}" >
                                            <i class="tio-delete"></i>
                                        </button>
                                        <form id="customer-{{$customer['id']}}" action="{{route('admin.customer.destroy',['id' => $customer['id']])}}" method="post">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4 px-3">
                    <div class="d-flex justify-content-lg-end">
                        {!! $customers->links() !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="add-point-modal" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" id="modal-content"></div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        function add_point(form_id, route, customer_id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: route,
                data: $('#' + form_id).serialize(),
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('.show-point-' + customer_id).text('( {{translate('Available Point : ')}} ' + data.updated_point + ' )');
                    $('.show-point-' + customer_id + '-table').text(data.updated_point);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }

        function set_point_modal_data(route) {
            $.get({
                url: route,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#add-point-modal').modal('show');
                    $('#modal-content').html(data.view);
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }
    </script>
@endpush
