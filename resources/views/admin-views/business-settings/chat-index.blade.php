@extends('layouts.admin.app')

@section('title', translate('Chat'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/third-party.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('third_party')}}
                </span>
            </h2>
        </div>

        @include('admin-views.business-settings.partials._3rdparty-inline-menu')

        <div class="row g-2">
            <div class="col-md-6">
                <div class="card">
                    @php($config=\App\CentralLogics\Helpers::get_business_settings('whatsapp'))
                    @if($config)
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.third-party.chat-update',['whatsapp']):'javascript:'}}"
                            method="post">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h5 class="mb-4">{{translate('Whatsapp')}}</h5>
                                    <label class="switcher">
                                        <input class="switcher_input" name="status" type="checkbox" {{$config['status'] == 1? 'checked' : ''}}>

                                        <span class="switcher_control"></span>
                                    </label>
                                </div>

                                @csrf
                                <div class="form-group">
                                    <label>{{translate('number')}} <span class="text-danger">({{ translate('without country code') }})</span></label><br>
                                    <input type="text" class="form-control" name="number"
                                           value="{{$config['number'] ?? ''}}" placeholder="{{ translate('WhatsApp Number') }}">
                                </div>
                                <div class="btn--container">
                                    <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                            class="btn btn-primary mb-2 call-demo">{{translate('save')}}
                                    </button>
                                </div>

                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection

