@extends('layouts.branch.app')

@section('title', translate('Business Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('business_setup')}}
                </span>
            </h2>
        </div>

        <form action="{{ route('branch.business-settings.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{translate('Branch Name')}}</label>
                                <input value="{{$branch['name']}}" type="text" name="name"  maxlength="255" class="form-control"
                                       placeholder="{{translate('Ex: ABC Branch')}}">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="input-label text-capitalize">{{translate('preparation_time')}}<span class="text-danger mx-1">*</span>
                                    <i class="tio-info-outined"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="{{ translate('Food preparation time will show to customer.') }}">
                                    </i>
                                </label>
                                <input value="{{ $branch['preparation_time'] }}" type="number" name="preparation_time" class="form-control"
                                       placeholder=" Ex: {{ translate('30') }}" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn--container mt-4">
                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary call-demo">{{translate('submit')}}</button>
            </div>
        </form>
    </div>
@endsection
