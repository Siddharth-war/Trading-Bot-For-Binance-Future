@extends('layouts.admin.app')

@section('title', translate('New Joining Request'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/deliveryman.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('New Joining Request')}}
                </span>
            </h2>
            <span class="badge badge-soft-dark rounded-circle fz-12">{{ $deliverymen->total() }}</span>
        </div>
        <div class="mb-4">
            <ul class="nav nav-tabs border-0">
                <li class="nav-item">
                    <a class="nav-link {{Request::is('admin/delivery-man/pending/list')?'active':''}}"  href="{{ route('admin.delivery-man.pending') }}">{{ translate('Pending Delivery Man') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{Request::is('admin/delivery-man/denied/list')?'active':''}}"  href="{{ route('admin.delivery-man.denied') }}">{{ translate('Denied Delivery Man') }}</a>
                </li>
            </ul>
        </div>


        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="d-flex flex-column flex-md-row flex-wrap gap-3 justify-content-md-between align-items-md-center">
                            <form action="{{url()->current()}}" method="GET">
                                <div class="input-group">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by Name or Phone or Email')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">
                                        {{translate('Search')}}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('name')}}</th>
                                        <th>{{translate('Contact_Info ')}}</th>
                                        <th class="text-center">{{translate('Branch')}}</th>
                                        <th class="text-center">{{translate('Identity Type')}}</th>
                                        <th class="text-center">{{translate('Identity Number')}}</th>
                                        <th class="text-center">{{translate('Identity Image')}}</th>
                                        <th class="text-center">{{translate('Status')}}</th>
                                        <th class="text-center">{{translate('action')}}</th>
                                    </tr>
                                </thead>

                                <tbody id="set-rows">
                                @foreach($deliverymen as $key=>$dm)
                                    <tr>
                                        <td>{{$deliverymen->firstitem()+$key}}</td>
                                        <td>
                                            <div class="media gap-3 align-items-center">
                                                <div class="avatar">
                                                    <img width="60" class="img-fit rounded-circle"
                                                         src="{{$dm->imageFullPath}}" alt="{{ translate('deliveryman') }}">
                                                </div>
                                                <div class="media-body">
                                                    {{$dm['f_name'].' '.$dm['l_name']}}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <div>
                                                    <a class="text-dark" href="mailto:{{$dm['email']}}">
                                                        <strong>{{$dm['email']}}</strong>
                                                    </a>
                                                </div>
                                                <a class="text-dark" href="tel:{{$dm['phone']}}">{{$dm['phone']}}</a>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($dm->branch_id == 0)
                                                <label class="badge badge-soft-primary">{{translate('All Branch')}}</label>
                                            @else
                                                <label class="badge badge-soft-primary">{{$dm->branch?$dm->branch->name:'Branch deleted!'}}</label>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ translate($dm->identity_type) }}</td>
                                        <td class="text-center">{{ $dm->identity_number }}</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2" data-toggle="" data-placement="top" title="{{translate('click for bigger view')}}">
                                                @foreach(json_decode($dm['identity_image'], true) as $identification_image)
                                                    @php($image_full_path = asset('storage/app/public/delivery-man'). '/' .$identification_image)
                                                    <div class="overflow-hidden">
                                                        <img class="cursor-pointer rounded img-fit custom-img-fit image-preview"
                                                             onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'"
                                                             src="{{$image_full_path}}">
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-info text-capitalize">{{ translate($dm->application_status) }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <div class="justify-content-center">
                                                <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                   data-toggle="tooltip" data-placement="top" title="{{translate('Approve')}}"
                                                   data-url="{{ route('admin.delivery-man.application', [$dm['id'], 'approved']) }}"
                                                   data-message="{{ translate('you_want_to_approve_this_application') }}"
                                                   href="#"><i class="tio-done font-weight-bold"></i></a>
                                                @if ($dm->application_status != 'denied')
                                                    <a class="btn btn-sm btn--danger btn-outline-danger action-btn"
                                                       data-toggle="tooltip" data-placement="top" title="{{translate('Deny')}}"
                                                       data-url="{{ route('admin.delivery-man.application', [$dm['id'], 'denied']) }}"
                                                       data-message="{{ translate('you_want_to_deny_this_application') }}"
                                                       href="#"><i class="tio-clear"></i></a>
                                                @endif
                                            </div>
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
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="identification_image_view_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div data-dismiss="modal">
                            <img onerror="this.src='{{asset('public/assets/admin/img/160x160/img1.jpg')}}'" alt=""
                                 class="" id="identification_image_element" width="100%">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $('.action-btn').click(function() {
            var url = $(this).data('url');
            var message = $(this).data('message');
            request_alert(url, message);
        });

        function request_alert(url, message) {
            Swal.fire({
                title: '{{ translate('are_you_sure') }}',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#2750E6',
                cancelButtonText: '{{ translate('no') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href = url;
                }
            })
        }

        $('.image-preview').click(function() {
            var imagePath = $(this).attr('src');
            show_modal(imagePath);
        });

        function show_modal(image_location) {
            $('#identification_image_view_modal').modal('show');
            if(image_location != null || image_location !== '') {
                $('#identification_image_element').attr("src", image_location);
            }
        }
    </script>
@endpush
