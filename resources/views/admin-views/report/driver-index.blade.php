@extends('layouts.admin.app')

@section('title', translate('Driver Report'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{ asset('public/assets/admin/img/icons/takeaway.png') }}"
                    alt="">
                <span class="page-header-title">
                    {{ translate('Deliveryman_Report') }}
                </span>
            </h2>
        </div>

        <div class="card mt-3">
            <div class="card-header border-0 pb-0">
                <div class="w-100">
                    <form action="javascript:" id="search-form">
                        @csrf
                        <div class="row align-items-end g-2 mb-3">
                            <div class="col-sm-6 col-md-5">
                                <div class="form-group mb-0">
                                    <label class="input-label">Deliveryman Joining Date</label>
                                    <input type="text" name="date" id="js-daterangepicker-predefined"
                                        class="form-control" required>
                                    <div class="js-daterangepicker-predefined-preview"></div>

                                </div>
                            </div>
                            <div class="col-sm-6 col-md-5">
                                <div class="form-group mb-0">
                                    <label class="input-label">Deliveryman Status</label>
                                    <select class="custom-select" name="delivery_man_id" id="delivery_man">
                                        <option value="0">Select Status</option>
                                        <option value="1">All</option>
                                        <option value="2">Active</option>
                                        <option value="3">Inctive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">{{ translate('filter') }}</button>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-top px-card pt-4">
                <div class="row justify-content-between align-items-center gy-2">
                    <div class="col-md-4 col-lg-6">
                        <h5 class="d-flex gap-1 mb-0">
                            Customer list
                            <span class="badge badge-soft-dark rounded-50 fz-12">203</span>
                        </h5>
                    </div>
                    <div class="col-md-8 col-lg-6">
                        <div class="d-flex gap-3 flex-wrap justify-content-end">
                            <form action="#" method="GET" class="flex-grow-1">
                                <div class="input-group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                        placeholder="Search by mail title, ID or customer type" aria-label="Search"
                                        value="" required="" autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </div>
                                </div>
                            </form>
                            <div>
                                <button type="button" class="btn btn-outline-primary text-nowrap" data-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="tio-download-to"></i>
                                    Export
                                    <i class="tio-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a type="submit" class="dropdown-item d-flex align-items-center gap-2"
                                            href="#">
                                            <img width="14" src="{{ asset('public/assets/admin/img/icons/excel.png') }}"
                                                alt="">
                                            Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="py-3">
                <div class="table-responsive datatable-custom">
                    <table
                        class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>SL</th>
                                <th>Deliveryman Info</th>
                                <th>Joining Date</th>
                                <th class="text-center">Total Orders</th>
                                <th class="text-center">Ongoing</th>
                                <th class="text-center">Cancel</th>
                                <th class="text-center">Completed</th>
                                <th class="text-center">Total Order Amount</th>
                                <th class="text-center">Active/Inactive</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                            <tr class="">
                                <td>1</td>
                                <td class="max-w300">
                                    <a class="text-dark media align-items-center gap-2" href="#">
                                        <div class="avatar">
                                            <img src="{{ asset('public/assets/admin/img//160x160/img1.jpg') }}"
                                                class="rounded-circle img-fit" alt="">
                                        </div>
                                        <div class="media-body text-truncate">
                                            Anika Tahosin
                                            <div class="opacity-lg">tom*****r@gmail.com</div>
                                        </div>
                                    </a>
                                </td>
                                <td>10 Jan 2024,
                                    <br>
                                    06:28 PM
                                </td>
                                <td class="text-center">09</td>
                                <td class="text-center">02</td>
                                <td class="text-center">03</td>
                                <td class="text-center">04</td>
                                <td class="text-center">$5687.00</td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <label class="switcher">
                                            <input id="194" data-url="#" type="checkbox"
                                                class="switcher_input status-change" checked="">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-success btn-sm square-btn"
                                            data-toggle="modal" data-target="#deliveryManDetailsModal">
                                            <i class="tio-visible"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-responsive mt-4 px-3">
                    <div class="d-flex justify-content-lg-end">
                        <nav>
                            <ul class="pagination">

                                <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
                                    <span class="page-link" aria-hidden="true">‹</span>
                                </li>
                                <li class="page-item active" aria-current="page"><span class="page-link">1</span></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#" rel="next" aria-label="Next »">›</a>
                                </li>
                            </ul>
                        </nav>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deliveryManDetailsModal" tabindex="-1" aria-labelledby="deliveryManDetailsModal"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border py-4 px-3">
                    <div class="custom-modal-close">
                        <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i
                                class="tio-clear"></i></button>
                    </div>
                    <div class="d-flex justify-content-between gap-3 flex-wrap mt-5 w-100">
                        <div class="media gap-3">
                            <img class="rounded-circle" width="100" height="100"
                                src="{{ asset('public/assets/admin/img/160x160/img1.jpg') }}" alt="Image Description">
                            <div class="media-body">
                                <h3 class="fz-22 text-dark mb-0">
                                    Tomas Porter
                                </h3>
                                <div>
                                    <span class="rating text-primary fz-18 font-weight-semibold">
                                        <i class="tio-star"></i>
                                        4.0
                                    </span>
                                    (52 Reviews)
                                </div>
                                <div>tom*****r@gmail.com</div>
                                <div>+8**************</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="flex-grow-1">
                                <div class="resturant-card dashboard--card border-0 shadow-none bg-FF5B7F-light">
                                    <div class="mr-4">
                                        <h4 class="title">34</h4>
                                        <span class="subtitle">
                                            Pending
                                        </span>
                                    </div>
                                    <div class="resturant-icon round-bg bg-FF5B7F">
                                        <img class="" width="16"
                                            src="{{ asset('public/assets/admin/img/modal/deliveryman-report/pending.svg') }}"
                                            alt="">
                                    </div>

                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="resturant-card dashboard--card border-0 shadow-none bg-BF83FF-light">
                                    <div class="mr-4">
                                        <h4 class="title">34</h4>
                                        <span class="subtitle">
                                            Out for Delivery
                                        </span>
                                    </div>
                                    <div class="resturant-icon round-bg bg-BF83FF">
                                        <img class="" width="16"
                                            src="{{ asset('public/assets/admin/img/modal/deliveryman-report/out-for-delivery.svg') }}"
                                            alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="resturant-card dashboard--card border-0 shadow-none bg-3CD856-light">
                                    <div class="mr-4">
                                        <h4 class="title">34</h4>
                                        <span class="subtitle">
                                            Completed
                                        </span>
                                    </div>
                                    <div class="resturant-icon round-bg bg-3CD856">
                                        <img class="" width="16"
                                            src="{{ asset('public/assets/admin/img/modal/deliveryman-report/completed.svg') }}"
                                            alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="resturant-card dashboard--card border-0 shadow-none bg-53B65A-light">
                                    <div class="mr-4">
                                        <h4 class="title">$4,560.24
                                        </h4>
                                        <span class="subtitle">
                                            Earned
                                        </span>
                                    </div>
                                    <div class="resturant-icon round-bg bg-53B65A">
                                        <img class="" width="16"
                                            src="{{ asset('public/assets/admin/img/modal/deliveryman-report/earned.svg') }}"
                                            alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body px-0">
                    <div class="d-flex justify-content-between align-items-center gap-3 px-card mb-3">
                        <div>
                            <h5 class="d-flex gap-1 mb-0">
                                Order List
                                <span class="badge badge-soft-dark rounded-50 fz-12">203</span>
                            </h5>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-primary text-nowrap" data-toggle="dropdown"
                                aria-expanded="false">
                                <i class="tio-download-to"></i>
                                Export
                                <i class="tio-chevron-down"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a type="submit" class="dropdown-item d-flex align-items-center gap-2"
                                        href="#">
                                        <img width="14" src="{{ asset('public/assets/admin/img/icons/excel.png') }}"
                                            alt="">
                                        Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Order ID</th>
                                    <th>Customer Info</th>
                                    <th>Order Date</th>
                                    <th class="text-center">Total Items</th>
                                    <th class="text-center">Order Amount</th>
                                    <th class="text-center">Payment Method</th>
                                    <th class="text-center">Branch</th>
                                    <th class="text-center">Order Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                                <tr class="">
                                    <td>1</td>
                                    <td>132456</td>
                                    <td>
                                        <div class="media-body text-truncate">
                                            Anika Tahosin
                                            <div class="opacity-lg">tom*****r@gmail.com</div>
                                        </div>
                                    </td>
                                    <td>
                                        10 Jan 2024,
                                        <br>
                                        06:28 PM
                                    </td>
                                    <td class="text-center">09</td>
                                    <td class="text-center">$5687.00</td>
                                    <td>Cash on Dleivery</td>
                                    <td>Main Brnach</td>
                                    <td>
                                        <span class="px-2 py-1 badge-soft-danger font-weight-bold fz-12 rounded lh-1">
                                            Canceled
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-success btn-sm square-btn">
                                                <i class="tio-visible"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{ asset('public/assets/admin') }}/vendor/chartjs-chart-matrix/dist/chartjs-chart-matrix.min.js"></script>
    <script src="{{ asset('public/assets/admin') }}/js/hs.chartjs-matrix.js"></script>

    <script>
        "use strict";

        $(document).on('ready', function() {
            $('.js-flatpickr').each(function() {
                $.HSCore.components.HSFlatpickr.init($(this));
            });

            $('.js-nav-scroller').each(function() {
                new HsNavScroller($(this)).init()
            });

            $('.js-daterangepicker').daterangepicker();

            $('.js-daterangepicker-times').daterangepicker({
                timePicker: true,
                startDate: moment().startOf('hour'),
                endDate: moment().startOf('hour').add(32, 'hour'),
                locale: {
                    format: 'M/DD hh:mm A'
                }
            });

            var start = moment();
            var end = moment();

            // function cb(start, end) {
            //     $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format(
            //         'MMM D') + ' - ' + end.format('MMM D, YYYY'));
            // }
            function cb(start, end) {
                $('#js-daterangepicker-predefined').val(start.format('D MMM, YYYY') + ' - ' + end.format(
                    'D MMM, YYYY'));
                $('#js-daterangepicker-predefined-preview').html(start.format('D MMM') + ' - ' + end.format(
                    'D MMM, YYYY'));
            }

            $('#js-daterangepicker-predefined').daterangepicker({
                startDate: start,
                endDate: end,
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
            }, cb);

            cb(start, end);


            $('.js-chart').each(function() {
                $.HSCore.components.HSChartJS.init($(this));
            });

            var updatingChart = $.HSCore.components.HSChartJS.init($('#updatingData'));

            $('[data-toggle="chart"]').click(function(e) {
                let keyDataset = $(e.currentTarget).attr('data-datasets')

                updatingChart.data.datasets.forEach(function(dataset, key) {
                    dataset.data = updatingChartDatasets[keyDataset][key];
                });
                updatingChart.update();
            })

            function generateHoursData() {
                var data = [];
                var dt = moment().subtract(365, 'days').startOf('day');
                var end = moment().startOf('day');
                while (dt <= end) {
                    data.push({
                        x: dt.format('YYYY-MM-DD'),
                        y: dt.format('e'),
                        d: dt.format('YYYY-MM-DD'),
                        v: Math.random() * 24
                    });
                    dt = dt.add(1, 'day');
                }
                return data;
            }

            $.HSCore.components.HSChartMatrixJS.init($('.js-chart-matrix'), {
                data: {
                    datasets: [{
                        label: 'Commits',
                        data: generateHoursData(),
                        width: function(ctx) {
                            var a = ctx.chart.chartArea;
                            return (a.right - a.left) / 70;
                        },
                        height: function(ctx) {
                            var a = ctx.chart.chartArea;
                            return (a.bottom - a.top) / 10;
                        }
                    }]
                },
                options: {
                    tooltips: {
                        callbacks: {
                            title: function() {
                                return '';
                            },
                            label: function(item, data) {
                                var v = data.datasets[item.datasetIndex].data[item.index];

                                if (v.v.toFixed() > 0) {
                                    return '<span class="font-weight-bold">' + v.v.toFixed() +
                                        ' hours</span> on ' + v.d;
                                } else {
                                    return '<span class="font-weight-bold">No time</span> on ' + v.d;
                                }
                            }
                        }
                    },
                    scales: {
                        xAxes: [{
                            position: 'bottom',
                            type: 'time',
                            offset: true,
                            time: {
                                unit: 'week',
                                round: 'week',
                                displayFormats: {
                                    week: 'MMM'
                                }
                            },
                            ticks: {
                                "labelOffset": 20,
                                "maxRotation": 0,
                                "minRotation": 0,
                                "fontSize": 12,
                                "fontColor": "rgba(22, 52, 90, 0.5)",
                                "maxTicksLimit": 12,
                            },
                            gridLines: {
                                display: false
                            }
                        }],
                        yAxes: [{
                            type: 'time',
                            offset: true,
                            time: {
                                unit: 'day',
                                parser: 'e',
                                displayFormats: {
                                    day: 'ddd'
                                }
                            },
                            ticks: {
                                "fontSize": 12,
                                "fontColor": "rgba(22, 52, 90, 0.5)",
                                "maxTicksLimit": 2,
                            },
                            gridLines: {
                                display: false
                            }
                        }]
                    }
                }
            });

            $('.js-clipboard').each(function() {
                var clipboard = $.HSCore.components.HSClipboard.init(this);
            });

            $('.js-circle').each(function() {
                var circle = $.HSCore.components.HSCircles.init($(this));
            });
        });

        $('#search-form').on('submit', function() {
            let formDate = $('#from_date').val();
            let toDate = $('#to_date').val();
            let delivery_man = $('#delivery_man').val();
            $.post({
                url: "{{ route('admin.report.deliveryman_filter') }}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    'formDate': formDate,
                    'toDate': toDate,
                    'delivery_man': delivery_man,
                },

                beforeSend: function() {
                    $('#loading').show();
                },
                success: function(data) {
                    console.log(data.delivered_qty)
                    $('#set-rows').html(data.view);
                    $('#delivered_qty').html(data.delivered_qty);
                    $('.card-footer').hide();
                },
                complete: function() {
                    $('#loading').hide();
                },
            });
        });

        $('#from_date,#to_date').change(function() {
            let fr = $('#from_date').val();
            let to = $('#to_date').val();
            if (fr != '' && to != '') {
                if (fr > to) {
                    $('#from_date').val('');
                    $('#to_date').val('');
                    toastr.error('{{ \App\CentralLogics\translate('Invalid date range!') }}', Error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            }

        });
    </script>
@endpush
