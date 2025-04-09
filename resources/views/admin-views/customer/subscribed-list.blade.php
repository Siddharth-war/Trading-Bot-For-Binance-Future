@extends('layouts.admin.app')

@section('title', translate('Subscribed List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/subscribers.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Subscribed_Customers')}}&nbsp;
                    <span class="badge badge-soft-dark rounded-50 fz-14"> {{ $newsletters->total() }}</span>
                </span>
            </h2>
        </div>

        <div class="card">

            <div class="d-flex flex-column flex-md-row flex-wrap gap-3 justify-content-md-between align-items-md-center">
                <form action="{{url()->current()}}" method="GET">
                    <div class="input-group">
                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by Email')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                        </div>
                    </div>
                </form>

                <div class="d-flex flex-wrap justify-content-md-end gap-3">
                    <div>
                        <button type="button" class="btn btn-outline-primary text-nowrap" data-toggle="dropdown" aria-expanded="false">
                            <i class="tio-download-to"></i>
                            Export
                            <i class="tio-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a type="submit" class="dropdown-item d-flex align-items-center gap-2" href="{{route('admin.customer.subscribed_emails_export', ['search'=>$search])}}">
                                    <img width="14" src="{{asset('public/assets/admin/img/icons/excel.png')}}" alt="">
                                    {{ translate('Excel') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="py-4">
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th class="">
                                    {{translate('SL')}}
                                </th>
                                <th>{{translate('email')}}</th>
                                <th>{{translate('Subscribed At')}}</th>
                            </tr>
                        </thead>

                        <tbody id="set-rows">
                        @foreach($newsletters as $key=>$newsletter)
                            <tr class="">
                                <td class="">
                                    {{$newsletters->firstitem()+$key}}
                                </td>
                                <td>
                                    <a class="text-dark" href="mailto:{{$newsletter['email']}}?subject={{translate('Mail from '). \App\Model\BusinessSetting::where(['key' => 'restaurant_name'])->first()->value}}">{{$newsletter['email']}}</a>
                                </td>
                                <td>{{date('d M Y h:m A '.config('timeformat'), strtotime($newsletter->created_at))}}</td>
                            </tr>

                        @endforeach

                        </tbody>
                    </table>
                </div>

                <div class="table-responsive px-3">
                    <div class="d-flex justify-content-lg-end">
                        {!! $newsletters->links() !!}
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
