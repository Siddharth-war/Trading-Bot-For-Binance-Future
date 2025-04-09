@extends('layouts.admin.app')

@section('title', translate('Language Translate'))

@push('css_or_js')
    <link href="{{asset('public/assets/admin')}}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <style>
        td{
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 500px;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <nav aria-label="breadcrumb w-100">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{translate('Dashboard')}}</a>
                </li>
                <li class="breadcrumb-item" aria-current="page">{{translate('Language')}}</li>
            </ol>
        </nav>

        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card w-100">
                    <div class="card-header">
                        <h5>{{translate('language_content_table')}}</h5>
                        <a href="{{route('admin.business-settings.web-app.system-setup.language.index')}}"
                           class="btn btn-sm btn-danger btn-icon-split float-right">
                            <span class="text text-capitalize">{{translate('back')}}</span>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable">
                                <thead>
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th style="width: 100px!important;">{{translate('key')}}</th>
                                    <th>{{translate('value')}}</th>
                                    <th>{{translate('action')}}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($lang_data as $count=>$language)
                                    <tr id="lang-{{$language['key']}}">
                                        <td>{{$count+1}}</td>
                                        <td>
                                            <div style="white-space: initial">
                                                <input type="text" name="key[]" value="{{$language['key']}}" hidden>
                                                <label>{{$language['key']}}</label>
                                            </div>
                                        </td>
                                        <td style="width: 90%; min-width: 300px;">
                                            <input type="text" class="form-control w-100" name="value[]"
                                                   id="value-{{$count+1}}" style="width: auto"
                                                   value="{{$language['value']}}">
                                        </td>
                                        <td style="width: 50%">
                                            <button type="button"
                                                    onclick="update_lang('{{urlencode($language['key'])}}',$('#value-{{$count+1}}').val())"
                                                    class="btn btn-primary">Update
                                            </button>
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
    @endsection

    @push('script')
            <script src="{{asset('public/assets/admin')}}/vendor/datatables/dataTables.bootstrap4.min.js"></script>
            <script>
                $(document).ready(function () {
                    $('#dataTable').DataTable({
                        "pageLength": '{{\App\CentralLogics\Helpers::getPagination()}}'
                    });
                });

                function update_lang(key, value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.business-settings.web-app.system-setup.language.translate-submit',[$lang])}}",
                        method: 'POST',
                        data: {
                            key: key,
                            value: value
                        },
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (response) {
                            toastr.success('{{translate('text_updated_successfully')}}');
                        },
                        complete: function () {
                            $('#loading').hide();
                        },
                    });
                }

                function remove_key(key) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.business-settings.web-app.system-setup.language.remove-key',[$lang])}}",
                        method: 'POST',
                        data: {
                            key: key
                        },
                        beforeSend: function () {
                            $('#loading').show();
                        },
                        success: function (response) {
                            toastr.success('{{translate('Key removed successfully')}}');
                            $('#lang-'+key).hide();
                        },
                        complete: function () {
                            $('#loading').hide();
                        },
                    });
                }
            </script>

    @endpush
