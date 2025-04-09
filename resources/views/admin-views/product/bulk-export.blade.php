@extends('layouts.admin.app')

@section('title', translate('Product Bulk Import'))

@section('content')

    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/bulk_import.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Bulk_Export')}}
                </span>
            </h2>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card">
                    <div class="p-2 pt-3">
                        <div class="export-steps">
                            <div class="export-steps-item">
                                <div class="inner">
                                    <h5>{{translate('STEP 1')}}</h5>
                                    <p>
                                        {{translate('Select_Data_Type')}}
                                    </p>
                                </div>
                            </div>
                            <div class="export-steps-item">
                                <div class="inner">
                                    <h5>{{translate('STEP 2')}}</h5>
                                    <p>
                                        {{translate('Select Data Range and Export')}}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form class="product-form px-3 pb-3" action="{{url()->current()}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('Type')}}</label>
                                        <select onchange="showHide(this)" name="type" id="type" data-placeholder="Select Type" class="form-control" required="" title="Select Type">
                                            <option value="all">{{translate('All Data')}}</option>
                                            <option value="date_wise">{{translate('Date Wise')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 date-wise-div d--none">
                                    <div class="form-group date_wise">
                                        <label class="input-label">{{translate('From Date')}}</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4 date-wise-div d--none">
                                    <div class="form-group date_wise">
                                        <label class="input-label">{{translate('To Date')}}</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-3 justify-content-end">
                                        <button class="btn btn-secondary" type="reset">{{translate('Clear')}}</button>
                                        <button class="btn btn-primary" type="submit" title="Bulk export">{{translate('Export')}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function showHide(t) {
            let selectValue = $(t).find(":selected").val()
            if(selectValue === 'all') {
                $('.date-wise-div').hide()
            } else if (selectValue === 'date_wise') {
                $('.date-wise-div').css('display', 'block')
            }
        }
    </script>

@endpush
