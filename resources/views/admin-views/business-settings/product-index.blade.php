@extends('layouts.admin.app')

@section('title', translate('Business Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/business_setup2.png')}}" alt="{{ translate('product_index') }}">
                <span class="page-header-title">
                    {{translate('business_setup')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._business-setup-inline-menu')

        <div>
            <form action="{{route('admin.business-settings.restaurant.search-placeholder-store')}}" method="post" id="search-placeholder-save-form">
                @csrf
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="mb-0">
                            {{translate('Add Search Placeholder')}}
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12">
                                <div class="form-group">
                                    <label class="input-label">{{translate('search_placeholder')}}</label>
                                    <input type="text" value="{{ old('placeholder_name') }}" name="placeholder_name" id="search-placeholder"
                                           class="form-control" placeholder="{{translate('ex: search here')}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary call-demo">{{translate('submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="row g-3">
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex gap-1 mb-0">
                                    {{translate('Search Placeholder Table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12"></span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('Placeholder_name')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php($data = isset($searchPlaceholder->value)?json_decode($searchPlaceholder->value, true):null)

                                @foreach($data ?? [] as $key=>$placeholder)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><div class="text-capitalize">{{$placeholder['placeholder_name']}}</div></td>
                                        <td>
                                            <div class="">
                                                <label class="switcher">
                                                    <input class="switcher_input change-status status-change" type="checkbox" {{$placeholder['status']==1? 'checked' : ''}} id="status-{{$placeholder['id']}}"
                                                           data-url="{{ route('admin.business-settings.restaurant.search-placeholder-status', $placeholder['id']) }}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-outline-info btn-sm edit square-btn edit-search-placeholder" data-key="{{$key}}">
                                                    <i class="tio-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="placeholder-{{$placeholder['id']}}"
                                                        data-message="{{translate("Want to delete this")}}?">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.business-settings.restaurant.search-placeholder-delete', $placeholder['id']) }}"
                                                  method="post" id="placeholder-{{$placeholder['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
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
            $(".edit-search-placeholder").on("click", function () {
                console.log('here');
                let key = $(this).data("key");
                console.log(key);

                let searchedData = @json($data ?? []);

                let selectedSearchedData = searchedData[key];
                console.log(selectedSearchedData);

                if (selectedSearchedData) {
                    $("#search-placeholder").val(selectedSearchedData.placeholder_name);
                    if ($("#id").length === 0) {
                        $("<input>")
                            .attr("type", "hidden")
                            .attr("id", "id")
                            .attr("name", "id")
                            .appendTo("#search-placeholder-save-form");
                    }

                    $("#id").val(selectedSearchedData.id);

                    $("html, body").animate({
                        scrollTop: $("#search-placeholder-save-form").offset().top
                    }, 500);
                }
            });
        })
    </script>
@endpush


