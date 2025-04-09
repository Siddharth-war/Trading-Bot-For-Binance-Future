@extends('layouts.admin.app')

@section('title', translate('Review List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/rating.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('review_List')}}
                </span>
            </h2>
        </div>

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex align-items-center gap-2">
                                    {{translate('Delivery_Men_Review_Table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $reviews->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by Name')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
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
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                data-hs-datatables-options='{
                                    "order": [],
                                    "orderCellsTop": true
                                }'>
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('deliveryman')}}</th>
                                        <th>{{translate('customer')}}</th>
                                        <th>{{translate('review')}}</th>
                                        <th class="text-center">{{translate('rating')}}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($reviews as $key=>$review)
                                    <tr>
                                        <td>{{$reviews->firstitem()+$key}}</td>
                                        <td>
                                            @if(isset($review->delivery_man))
                                                <div>
                                                    <a class="text-dark" href="{{route('admin.delivery-man.preview',[$review['delivery_man_id']])}}">
                                                        {{$review->delivery_man->f_name.' '.$review->delivery_man->l_name}}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted small">
                                                        {{translate('Deliveryman_Unavailable')}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($review->customer))
                                                <div>
                                                    <a class="text-dark" href="{{route('admin.customer.view',[$review->user_id])}}">
                                                        {{$review->customer->f_name." ".$review->customer->l_name}}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted small">
                                                    {{translate('Customer unavailable')}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="max-w300 line-limit-3">
                                                {{$review->comment??''}}
                                            </div>
                                        </td>
                                        <td class="d-flex justify-content-center">
                                            <div class="badge badge-soft-info d-inline-flex align-items-center gap-1">
                                                {{$review->rating??0}} <i class="tio-star"></i>
                                            </div>
                                        </td>
                                    </tr>

                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $reviews->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/review-list.js')}}"></script>
@endpush
