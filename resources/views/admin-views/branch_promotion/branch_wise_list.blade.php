@extends('layouts.admin.app')

@section('title', translate('Promotional Campaign'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('branch wise campaign')}} - {{ $branch->name }}</h1>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header flex-between">
                    <div class="">
                        <h5>{{translate('Promotional Campaign Table')}}
                            <span class="badge badge-soft-dark rounded-50 fz-12">{{$promotions->total()}}</span>
                        </h5>
                    </div>
                    <div class="d-flex">
                        <h5 class="pr-3">{{translate('Promotion Status')}}</h5>
                        <label class="switcher">
                            <input id="31" class="switcher_input redirect-url" type="checkbox"
                                   data-url="{{route('admin.promotion.status',[$branch['id'],$branch->branch_promotion_status?0:1])}}"
                                {{$branch->branch_promotion_status?'checked':''}}>
                            <span class="switcher_control"></span>
                        </label>
                    </div>
                    <div class="flex-end">
                        <div class="mx-2">
                            <form action="{{url()->current()}}" method="GET">
                                <div class="input-group">
                                    <input id="datatableSearch_" type="search" name="search"
                                           class="form-control"
                                           placeholder="{{translate('Search')}}" aria-label="Search"
                                           value="{{$search}}" required autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" class="input-group-text"><i class="tio-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table table-width">
                            <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Promotion type')}}</th>
                                <th>{{translate('Promotion Name')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($promotions as $k=>$promotion)
                                <tr>
                                    <th scope="row">{{$k+1}}</th>
                                    <td>
                                        @php
                                            $promotion_type = $promotion['promotion_type'];
                                            echo str_replace('_', ' ', $promotion_type);
                                        @endphp
                                    </td>
                                    <td>
                                        @if($promotion['promotion_type'] == 'video')
                                            {{$promotion['promotion_name']}}
                                        @else
                                            <div class="promotion-image-section">
                                                <img class="mx-80px" src="{{asset('storage/app/public/promotion')}}/{{$promotion['promotion_name']}}">
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{$promotions->links()}}
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/read-url.js')}}"></script>
@endpush
