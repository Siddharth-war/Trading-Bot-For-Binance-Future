@extends('layouts.admin.app')

@section('title', translate('Settings'))

@section('content')
    <div class="content container-fluid">
    @php($branch_count=\App\Model\Branch::count())
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/location.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Location_Coverage_Setup')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.restaurant.update-location'):'javascript:'}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            @php($data=\App\Model\Branch::find(1))
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <span class="badge-soft-danger px-2">
                                        {{translate('This location setup is for your Main branch. Carefully set your restaurant location and coverage area. If you want to ignore the coverage area then keep the input box empty.')}}
                                        {{translate('You can ignore this when you have only the default branch and you do not want coverage area.')}}
                                    </span>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('latitude')}}</label>
                                        <input type="text" value="{{$data['latitude']}}"
                                            name="latitude" class="form-control"
                                            placeholder="{{translate('Ex : -94.22213')}}" {{$branch_count>1?'required':''}}>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('longitude')}}</label>
                                        <input type="text" value="{{$data['longitude']}}"
                                            name="longitude" class="form-control"
                                            placeholder="{{translate('Ex : 103.344322')}}" {{$branch_count>1?'required':''}}>
                                    </div>
                                </div>

                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="">
                                            <i class="tio-info-outined"
                                            data-toggle="tooltip"
                                            data-placement="top"
                                            title="{{translate('This value is the radius from your restaurant location, and customer can order food inside  the circle calculated by this radius.')}}"></i>
                                            {{translate('coverage')}} ( {{translate('km')}} )
                                        </label>
                                        <input type="number" value="{{$data['coverage']}}"
                                            name="coverage" class="form-control" placeholder="{{translate('Ex : 3')}}" {{$branch_count>1?'required':''}}>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary call-demo">{{translate('update')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
