@extends('layouts.admin.app')

@section('title', translate('FAQ'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('FAQ')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._page-setup-inline-menu')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form
                            action="{{route('admin.business-settings.page-setup.refund_page_update')}}" id="tnc-form" method="post">
                            @csrf

                            <div class="d-flex gap-3 align-items-center mb-3">
                                <div class="text-dark font-weight-bold">{{ translate('Check Status') }}</div>
                                <label class="switcher">
                                    <input type="checkbox" class="switcher_input" name="status"
                                           value="1" {{ json_decode($data['value'],true)['status']==1?'checked':''}}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>

                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea class="ckeditor form-control" name="content">
                                            {{ json_decode($data['value'],true)['content']}}
                                        </textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 align-items-center">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary call-demo">{{translate('save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.ckeditor').ckeditor();
        });
    </script>
@endpush
