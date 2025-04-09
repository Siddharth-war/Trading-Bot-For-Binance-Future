@extends('layouts.admin.app')

@section('title', translate('Vendor List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/customer.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Vendor')}}
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


                        <a href="{{ route('admin.vendor.vendor_add')}}"  class="btn btn-primary">Add Vendor</a>
                    </div>
                </div>
            </div>

            <div class="py-3">
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('vendor_Name')}}</th>
                                <th>{{translate('vendor_email')}}</th>
                                <th>{{translate('vendor_phone')}}</th>
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
                                        {{-- <div class="avatar">
                                            <img src="{{asset('/public/storage/vendor'.'/'.$customer->image)}}" class="rounded-circle img-fit" alt="">
                                        </div> --}}
                                        <div class="media-body text-truncate">{{$customer['first_name']." ".$customer['last_name']}}</div>
                                    </a>
                                </td>
                                <td>
                                    <div><a href="mailto:{{$customer['email']}}" class="text-dark"><strong>{{$customer['email']}}</strong></a></div>

                                </td>
                                <td>
                                    <div><a class="text-dark" href="tel:{{$customer['phone_number']}}">{{$customer['phone_number']}}</a></div>
                                </td>

                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="btn btn-outline-success btn-sm square-btn"
                                           href="{{route('admin.vendor.view',[$customer['id']])}}">
                                            <i class="tio-visible"></i>
                                        </a>

                                        <a class="btn btn-outline-success btn-sm square-btn"
                                           href="{{route('admin.vendor.vendor_edit',[$customer['id']])}}">
                                            <i class="tio-edit"></i>
                                        </a>

                                        <button class="btn btn-outline-danger btn-sm square-btn form-alert"  data-id="customer-{{$customer['id']}}" data-message="{{translate('delete_this_user')}}" >
                                            <i class="tio-delete"></i>
                                        </button>
                                        <form id="customer-{{$customer['id']}}" action="{{route('admin.vendor.destroy',['id' => $customer['id']])}}" method="post">
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
