@extends('layouts.admin.app')

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

        @include('admin-views.business-settings.partials._business-setup-inline-menu')

        <section class="qr-code-section">
            <div class="card">
                <div class="card-body">
                    <div class="qr-area">
                        <div class="left-side pr-xl-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="text-dark w-0 flex-grow-1">{{ translate('QR Card Design') }}</div>
                                <div class="btn--container flex-nowrap print-btn-grp">
                                    <a type="button" href="{{ route('admin.business-settings.restaurant.qrcode.print') }}" class="btn btn-primary pt-1"><i class="tio-print"></i> {{translate('Print')}}</a>
                                </div>
                            </div>
                            @php($restaurantLogo=\App\Model\BusinessSetting::where(['key'=>'logo'])->first()?->value)
                            <div class="qr-wrapper" style="background: url({{asset('public/assets/admin/img/qr-bg.png')}}) no-repeat center center / 100% 100%">
                                <a href="#" class="qr-logo">
                                    <img src="{{asset('storage/app/public/qrcode/'.$data['logo'])}}" class="mw-100"
                                         onerror="this.src='{{asset('public/assets/admin/img/logo2.png')}}'" alt="">

                                </a>
                                <p class="view-menu-title">
                                    {{ isset($data) ? $data['title'] : translate('title') }}
                                </p>
                                <div class="text-center mt-4">
                                    <div>
                                        <img src="{{asset('public/assets/admin/img/scan-me.png')}}" class="mw-100" alt="">
                                    </div>
                                    <div class="my-3">
                                        {!! $code !!}
                                    </div>
                                </div>
                                <div class="subtext">
                                    <span>
                                        {{ isset($data) ? $data['description'] : translate('description') }}
                                    </span>
                                </div>
                                <div class="open-time">
                                    <div>{{ translate('OPEN DAILY') }}</div>
                                    <div>{{ isset($data) ? $data['opening_time'] : '09:00 AM' }} - {{ isset($data) ? $data['closing_time'] : '09:00 PM' }}</div>
                                </div>
                                <div class="phone-number">
                                    {{ translate('PHONE NUMBER') }} : {{ isset($data) ? $data['phone'] : '+00 123 4567890' }}
                                </div>
                                <div class="row g-0 text-center bottom-txt">
                                    <div class="col-6 border-right py-3 px-2">
                                        {{ isset($data) ? $data['website'] : 'www.website.com' }}
                                    </div>
                                    <div class="col-6 py-3">
                                        {{ isset($data) ? $data['social_media'] : translate('@social-media-name') }}

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="right-side">
                            <form method="post" action="{{ route('admin.business-settings.restaurant.qrcode.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-12" id="branch_section">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Branch')}}</label>
                                            <select class="form-control js-select2-custom" name="branch_id">
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Logo / Icon')}}</label>
                                            <label class="custom-file">
                                                <input type="file" name="logo" class="custom-file-input"
                                                       accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                <span class="custom-file-label">{{translate('choose_File')}}</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Title')}}</label>
                                            <input type="text" name="title" placeholder="{{ translate('Ex : Title') }}" class="form-control" value="{{old('title')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Description')}}</label>
                                            <input type="text" name="description" placeholder="{{ translate('Ex : Description') }}" value="{{old('description')}}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Opening Time')}}</label>
                                            <input type="time" class="form-control" name="opening_time" value="{{old('opening_time')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Closing Time')}}</label>
                                            <input type="time" class="form-control" name="closing_time" value="{{old('closing_time')}}" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Phone')}}</label>
                                            <input type="text" name="phone" placeholder="{{ translate('Ex : +123456') }}" value="{{old('phone')}}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Website Link')}}</label>
                                            <input type="text" name="website" value="{{old('website')}}" placeholder="{{ translate('Ex : www.website.com') }}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="input-label">{{translate('Social Media Name')}}</label>
                                            <input type="text" placeholder="{{ translate('@social media name')  }}" name="social_media" value="{{old('social_media')}}" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="btn--container">
                                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                            <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

