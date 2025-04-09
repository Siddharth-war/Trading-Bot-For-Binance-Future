@extends('layouts.admin.app')

@section('title', translate('Settings'))

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

        @include('admin-views.business-settings.partials._business-setup-inline-menu')

        <div class="row g-2">
            <div class="col-12">
                <div class="row">
                    <div class="col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{route('admin.business-settings.restaurant.cookies-setup-update')}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @php($cookies=\App\CentralLogics\Helpers::get_business_settings('cookies'))
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <span class="text-dark">{{translate('Cookie Text')}}</span>
                                        <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                            <input type="checkbox" name="status" value="1" class="toggle-switch-input" {{$cookies?($cookies['status']==1?'checked':''):''}}>
                                            <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                        </label>
                                    </div>
                                    <div class="form-group pt-3">
                                        <textarea name="text" class="form-control" rows="6" placeholder="{{ translate('Cookies text') }}" required>{{$cookies['text']}}</textarea>
                                    </div>
                                    <div class="btn--container justify-content-end">
                                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                                class="btn btn-primary call-demo">{{translate('save')}}</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
@endsection

