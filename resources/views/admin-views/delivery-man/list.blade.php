@extends('layouts.admin.app')

@section('title', translate('Deliveryman List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{ asset('public/assets/admin/img/icons/deliveryman.png') }}"
                    alt="">
                <span class="page-header-title">
                    {{ translate('Deliveryman_List') }}
                </span>
            </h2>
            <span class="badge badge-soft-dark rounded-circle fz-12">{{ $deliverymen->total() }}</span>
        </div>

        <div class="card mt-3">
            <div class="card-body border-0">
                <form action="{{ url()->current() }}" method="GET" id="search-form">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <div class="d-flex gap-4 justify-content-between align-items-end flex-wrap">
                        <div class="form-group flex-grow-1 mb-0">
                            <label class="input-label">{{ translate('Deliveryman Joining Date') }}</label>
                            <div class="position-relative">
                                <span class="tio-calendar icon-absolute-on-right"></span>
                                <input type="text" name="date" id="js-daterangepicker-predefined" class="form-control"
                                    placeholder="{{ translate('Select Date') }}" value="{{ request()->get('date') }}" autocomplete="off">

                            </div>
                            {{-- <div class="js-daterangepicker-predefined-preview"></div> --}}
                        </div>
                        <div class="form-group flex-grow-1 mb-0">
                            <label class="input-label">{{ translate('Deliveryman Status') }}</label>
                            <select class="custom-select" name="status" id="status">
                                <option value="all">{{ translate('All') }}</option>
                                <option value="active" {{ $status == 'active' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                                <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary px-6">{{ translate('filter') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-5 py-4">
            <div class="flex-grow-1">
                <div class="resturant-card dashboard--card px-3 pb-0 min-h-108px border-0 shadow-none bg--1">
                    <div class="mr-4">
                        <h4 class="title text-c2">{{ $deliverymen->total() }}</h4>
                        <span class="subtitle text-title">
                            {{ translate('Total Deliveryman') }}
                        </span>
                    </div>
                    <div class="resturant-icon">
                        <img class="" width="38"
                            src="{{ asset('public/assets/admin/img/deliveryman/total.png') }}" alt="">
                    </div>

                </div>
            </div>
            <div class="flex-grow-1">
                <div class="resturant-card dashboard--card px-3 pb-0 min-h-108px border-0 shadow-none bg--2">
                    <div class="mr-4">
                        <h4 class="title text-success">
                            {{ $activeCount }}</h4>
                        <span class="subtitle text-title">
                            {{ translate('Active Deliveryman') }}
                        </span>
                    </div>
                    <div class="resturant-icon">
                        <img class="" width="38"
                            src="{{ asset('public/assets/admin/img/deliveryman/active.png') }}" alt="">
                    </div>
                </div>
            </div>
            <div class="flex-grow-1">
                <div class="resturant-card dashboard--card px-3 pb-0 min-h-108px border-0 shadow-none bg--3">
                    <div class="mr-4">
                        <h4 class="title text-danger">
                            {{ $inactiveCount }}</h4>
                        <span class="subtitle text-title">
                            {{ translate(' Inactive Deliveryman') }}
                        </span>
                    </div>
                    <div class="resturant-icon">
                        <img class="" width="38"
                            src="{{ asset('public/assets/admin/img/deliveryman/inactive.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div
                            class="d-flex flex-column flex-sm-row flex-wrap gap-md-4 gap-3 justify-content-end align-items-sm-center">
                            <div class="flex-grow-1">
                                <h5 class="d-flex gap-1 mb-0">
                                    {{ translate('Deliveryman List') }}
                                    <span
                                        class="badge badge-soft-dark rounded-50 fz-12 ml-1">{{ $deliverymen->total() }}</span>
                                </h5>
                            </div>
                            <form action="{{ url()->current() }}" method="GET" class="flex-grow-1" id="searchForm">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <input type="hidden" name="date" value="{{ request()->get('date') }}" id="hiddenDate">
                                <div class="input-group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="{{ translate('Search by name or email or phone') }}"
                                        aria-label="Search" value="{{ $search }}" autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                            {{ translate('Search') }}
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div class="d-flex flex-wrap justify-content-end gap-3">
                                <div>
                                    <button type="button" class="btn btn-outline-primary text-nowrap px--12px"
                                        data-toggle="dropdown" aria-expanded="false">
                                        <i class="tio-download-to"></i>
                                        Export
                                        <i class="tio-chevron-down"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li>
                                            <a type="submit" class="dropdown-item d-flex align-items-center gap-2"
                                                href="{{ route('admin.delivery-man.excel-export', [
                                                    'status' => request('status'),
                                                    'search' => request('search'),
                                                    'date' =>  request()->get('date'),
                                                ]) }}">
                                                <img width="14"
                                                    src="{{ asset('public/assets/admin/img/icons/excel.png') }}"
                                                    alt="">
                                                {{ translate('Excel') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <a href="{{ route('admin.delivery-man.add') }}" class="btn btn-primary px--12px">
                                    <i class="tio-add"></i>
                                    {{ translate('add_Deliveryman') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('name') }}</th>
                                        <th>{{ translate('Contact_Info ') }}</th>
                                        <th>{{ translate('Joining_Date ') }}</th>
                                        <th class="text-center">{{ translate('Total_Orders') }}</th>
                                        <th class="text-center">{{ translate('Ongoing') }}</th>
                                        <th class="text-center">{{ translate('Cancel') }}</th>
                                        <th class="text-center">{{ translate('Completed') }}</th>
                                        <th class="text-center">{{ translate('Total_Order_Amount') }}</th>
                                        <th>{{ translate('Status') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>

                                <tbody id="set-rows">
                                    @foreach ($deliverymen as $key => $dm)
                                        <tr>
                                            <td>{{ $deliverymen->firstitem() + $key }}</td>
                                            <td>
                                                <div class="media gap-3 align-items-center">
                                                    <div class="avatar">
                                                        <img width="60" class="img-fit rounded-circle"
                                                            src="{{ $dm->imageFullPath }}"
                                                            alt="{{ translate('deliveryman') }}">
                                                    </div>
                                                    <div class="media-body">
                                                        <a class="text-dark"
                                                            href="{{ route('admin.delivery-man.details', [$dm['id']]) }}">{{ $dm['f_name'] . ' ' . $dm['l_name'] }}</a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <div>
                                                        <a class="text-dark" href="mailto:{{ $dm['email'] }}">
                                                            {{ $dm['email'] }}
                                                        </a>
                                                    </div>
                                                    <a class="text-dark"
                                                        href="tel:{{ $dm['phone'] }}">{{ $dm['phone'] }}</a>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $dm->created_at->format('d M Y,') }}<br>
                                                {{ $dm->created_at->format('h:i A') }}
                                            </td>
                                            <td class="text-center">
                                                {{ $dm['orders_count'] }}
                                            </td>
                                            <td class="text-center">{{ $dm->ongoing_orders_count }}</td>
                                            <td class="text-center">{{ $dm->canceled_orders_count }}</td>
                                            <td class="text-center">{{ $dm->completed_orders_count }}</td>
                                            <td class="text-center">{{ Helpers::set_symbol($dm->total_order_amount) }}
                                            </td>
                                            <td>
                                                <label class="switcher">
                                                    <input id="{{ $dm['id'] }}" type="checkbox"
                                                        class="switcher_input change-deliveryman-status"
                                                        {{ $dm['is_active'] == 1 ? 'checked' : '' }}
                                                        data-url="{{ route('admin.delivery-man.ajax-is-active', ['id' => $dm['id']]) }}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-10">
                                                    <a class="btn btn-outline-info btn-sm edit square-btn"
                                                        href="{{ route('admin.delivery-man.details', [$dm['id']]) }}">
                                                        <i class="tio-visible"></i>
                                                    </a>
                                                    <a class="btn btn-outline-info btn-sm edit square-btn"
                                                        href="{{ route('admin.delivery-man.edit', [$dm['id']]) }}"><i
                                                            class="tio-edit"></i></a>
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="delivery-man-{{ $dm['id'] }}"
                                                        data-message="{{ translate('Want to remove this information ?') }}"><i
                                                            class="tio-delete"></i></button>
                                                </div>
                                                <form action="{{ route('admin.delivery-man.delete', [$dm['id']]) }}"
                                                    method="post" id="delivery-man-{{ $dm['id'] }}">
                                                    @csrf @method('delete')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                        <div class="table-responsive px-3 mt-3">
                            <div class="d-flex justify-content-end">
                                {!! $deliverymen->links() !!}
                            </div>
                        </div>
                        @if (count($deliverymen) == 0)
                            <div class="text-center p-4">
                                <img class="w-120px mb-3"
                                    src="{{ asset('public/assets/admin/svg/illustrations/sorry.svg') }}"
                                    alt="{{ translate('image') }}">
                                <p class="mb-0">{{ translate('No_data_to_show') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    @endsection

    @push('script_2')
        <script>
            "use strict";

            $(document).on('ready', function() {
                var start = "{{ request()->get('date') ? explode(' - ', request()->get('date'))[0] : null }}";
                var end = "{{ request()->get('date') ? explode(' - ', request()->get('date'))[1] : null }}";

                start = start ? moment(start, 'D MMM, YYYY') : null;
                end = end ? moment(end, 'D MMM, YYYY') : null;

                function cb(start, end) {
                    $('#js-daterangepicker-predefined').val(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));
                    $('.js-daterangepicker-predefined-preview').html(start.format('D MMM') + ' - ' + end.format('D MMM, YYYY'));
                }

                $('#js-daterangepicker-predefined').daterangepicker({
                    autoUpdateInput: false,
                    startDate: start || moment(),
                    endDate: end || moment(),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')]
                    },
                    locale: {
                        format: 'D MMM, YYYY'
                    }
                });

                // Update the input field and preview when a range is selected
                $('#js-daterangepicker-predefined').on('apply.daterangepicker', function(ev, picker) {
                    cb(picker.startDate, picker.endDate);
                });

                // Clear the input and preview on cancel
                $('#js-daterangepicker-predefined').on('cancel.daterangepicker', function() {
                    $(this).val('');
                    $('.js-daterangepicker-predefined-preview').html('');
                });


                if (start && end) {
                    cb(start, end);
                }
            });



            $(".change-deliveryman-status").change(function() {
                var value = $(this).val();
                let url = $(this).data('url');
                statusChange(this, url);
            });

            function statusChange(t, url) {
                let checked = $(t).prop("checked");
                let status = checked === true ? 1 : 0;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Want to change status',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2750E6',
                    cancelButtonColor: 'default',
                    cancelButtonText: '{{ translate('No') }}',
                    confirmButtonText: '{{ translate('Yes') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url: url,
                            data: {
                                status: status
                            },
                            success: function(data, status) {
                                toastr.success("{{ translate('Status changed successfully') }}");
                                setInterval(function() {
                                    location.reload();
                                }, 1000);
                            },
                            error: function(data) {
                                toastr.error("{{ translate('Status changed failed') }}");
                            }
                        });
                    } else if (result.dismiss) {
                        if (status == 1) {
                            $(t).prop('checked', false);
                        } else if (status == 0) {
                            $(t).prop('checked', true);
                        }
                        toastr.info("{{ translate('Status has not changed') }}");
                    }
                });
            }
        </script>
    @endpush
