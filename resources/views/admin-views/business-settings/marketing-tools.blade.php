@extends('layouts.admin.app')

@section('title', translate('Marketing Tools'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin/vendor/swiper/swiper-bundle.min.css') }}" />
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
            <h2 class="h1 text-title mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{ asset('/public/assets/admin/img/icons/marketing-tools.svg') }}"
                    alt="">
                {{ translate('Marketing_Tool') }}
            </h2>
            <a class="btn btn-outline-primary opacity-20 btn-sm cursor-pointer font-weight-medium border-radius-35"
                data-toggle="modal" data-target="#settingModal">
                <i class="tio-help-outlined"></i>
                <span class="text-dark">{{ translate('How_It_Work') }}</span>
            </a>
        </div>
        <div class="row gy-3">
            <div class="col-lg-6">
                <div class="card">
                    @php($meta = \App\CentralLogics\Helpers::get_business_settings('meta_pixel'))
                    <div class="card-body">
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.third-party.update-marketing-tools', ['meta']):'javascript:'}}" method="post">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 text-title fz-16">
                                    {{ translate('Meta_Pixel') }}
                                </h4>
                                <div class="custom--switch">
                                    <input type="checkbox" name="status" value="" id="meta-pixel" switch="primary"
                                           class="toggle-switch-input" {{ isset($meta) && $meta['status'] ? 'checked' : '' }}>
                                    <label for="meta-pixel" data-on-label="on" data-off-label="off"></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="text-title d-flex font-weight-medium">{{ translate('Meta APP ID') }}</label>
                                <input type="text" class="form-control" name="meta_app_id" value="{{ isset($meta) ? $meta['meta_app_id'] : '' }}" placeholder="{{ translate('Enter Meta APP ID') }}">
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <a class="font-weight-medium cursor-pointer" data-toggle="modal"
                                    data-target="#singleInstructionModal">
                                    <i class="tio-help-outlined"></i>
                                    {{ translate('How_It_Work') }}
                                </a>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary px-4 call-demo">{{translate('submit')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
{{--            <div class="col-lg-6">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <form action="" method="post" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="d-flex justify-content-between gap-2 mb-5">--}}
{{--                                <h4 class="mb-0 d-flex gap-1 text-title fz-16">--}}
{{--                                    {{ translate('TikTok_Pixel') }}--}}
{{--                                </h4>--}}
{{--                                <div class="custom--switch">--}}
{{--                                    <input type="checkbox" name="status" value="" id="tiktok-pixel" switch="primary"--}}
{{--                                        class="toggle-switch-input">--}}
{{--                                    <label for="tiktok-pixel" data-on-label="on" data-off-label="off"></label>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group">--}}
{{--                                <label--}}
{{--                                    class="text-title d-flex font-weight-medium">{{ translate('TikTok Pixel ID') }}</label>--}}
{{--                                <input type="hidden" name="type" value="">--}}
{{--                                <textarea type="text" placeholder="{{ translate('Enter TikTok Pixel ID') }}" class="form-control h--45px"--}}
{{--                                    name="value"></textarea>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex gap-3 justify-content-between align-items-center">--}}
{{--                                <a class="font-weight-medium cursor-pointer" data-toggle="modal"--}}
{{--                                    data-target="#singleInstructionModal">--}}
{{--                                    <i class="tio-help-outlined"></i>--}}
{{--                                    {{ translate('How_It_Work') }}--}}
{{--                                </a>--}}
{{--                                <button type="button" class="btn btn-primary px-4" id="" disabled>--}}
{{--                                    {{ translate('Submit') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-lg-6">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <form action="" method="post" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="d-flex justify-content-between gap-2 mb-5">--}}
{{--                                <h4 class="mb-0 d-flex gap-1 text-title fz-16">--}}
{{--                                    {{ translate('X (Twitter) Pixel') }}--}}
{{--                                </h4>--}}
{{--                                <div class="custom--switch">--}}
{{--                                    <input type="checkbox" name="status" value="" id="twitter-pixel"--}}
{{--                                        switch="primary" class="toggle-switch-input">--}}
{{--                                    <label for="twitter-pixel" data-on-label="on" data-off-label="off"></label>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group">--}}
{{--                                <label class="text-title d-flex font-weight-medium">{{ translate('X Pixel ID') }}</label>--}}
{{--                                <input type="hidden" name="type" value="">--}}
{{--                                <textarea type="text" placeholder="{{ translate('Enter X Pixel ID') }}" class="form-control h--45px"--}}
{{--                                    name="value"></textarea>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex gap-3 justify-content-between align-items-center">--}}
{{--                                <a class="font-weight-medium cursor-pointer" data-toggle="modal"--}}
{{--                                    data-target="#singleInstructionModal">--}}
{{--                                    <i class="tio-help-outlined"></i>--}}
{{--                                    {{ translate('How_It_Work') }}--}}
{{--                                </a>--}}
{{--                                <button type="button" class="btn btn-primary px-4" id="" disabled>--}}
{{--                                    {{ translate('Submit') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-lg-6">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <form action="" method="post" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="d-flex justify-content-between gap-2 mb-5">--}}
{{--                                <h4 class="mb-0 d-flex gap-1 text-title fz-16">--}}
{{--                                    {{ translate('Pinterest_Pixel') }}--}}
{{--                                </h4>--}}
{{--                                <div class="custom--switch">--}}
{{--                                    <input type="checkbox" name="status" value="" id="pinterest-pixel"--}}
{{--                                        switch="primary" class="toggle-switch-input">--}}
{{--                                    <label for="pinterest-pixel" data-on-label="on" data-off-label="off"></label>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group">--}}
{{--                                <label--}}
{{--                                    class="text-title d-flex font-weight-medium">{{ translate('Pinterest Tag ID') }}</label>--}}
{{--                                <input type="hidden" name="type" value="">--}}
{{--                                <textarea type="text" placeholder="{{ translate('Enter Pinterest Tag ID') }}" class="form-control h--45px"--}}
{{--                                    name="value"></textarea>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex gap-3 justify-content-between align-items-center">--}}
{{--                                <a class="font-weight-medium cursor-pointer" data-toggle="modal"--}}
{{--                                    data-target="#singleInstructionModal">--}}
{{--                                    <i class="tio-help-outlined"></i>--}}
{{--                                    {{ translate('How_It_Work') }}--}}
{{--                                </a>--}}
{{--                                <button type="button" class="btn btn-primary px-4" id="" disabled>--}}
{{--                                    {{ translate('Submit') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-lg-6">--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <form action="" method="post" enctype="multipart/form-data">--}}
{{--                            @csrf--}}
{{--                            <div class="d-flex justify-content-between gap-2 mb-5">--}}
{{--                                <h4 class="mb-0 d-flex gap-1 text-title fz-16">--}}
{{--                                    {{ translate('Google_Tag_Manager') }}--}}
{{--                                </h4>--}}
{{--                                <div class="custom--switch">--}}
{{--                                    <input type="checkbox" name="status" value="" id="google_tag_manager"--}}
{{--                                        switch="primary" class="toggle-switch-input">--}}
{{--                                    <label for="google_tag_manager" data-on-label="on" data-off-label="off"></label>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="form-group">--}}
{{--                                <label--}}
{{--                                    class="text-title d-flex font-weight-medium">{{ translate('Container ID') }}</label>--}}
{{--                                <input type="hidden" name="type" value="">--}}
{{--                                <textarea type="text" placeholder="{{ translate('Enter Container ID') }}" class="form-control h--45px"--}}
{{--                                    name="value"></textarea>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex gap-3 justify-content-between align-items-center">--}}
{{--                                <a class="font-weight-medium cursor-pointer" data-toggle="modal"--}}
{{--                                    data-target="#singleInstructionModal">--}}
{{--                                    <i class="tio-help-outlined"></i>--}}
{{--                                    {{ translate('How_It_Work') }}--}}
{{--                                </a>--}}
{{--                                <button type="button" class="btn btn-primary px-4" id="" disabled>--}}
{{--                                    {{ translate('Submit') }}--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                        </form>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.web-app.third-party.update-marketing-tools', ['google']):'javascript:'}}" method="post">
                            @csrf
                            <div class="d-flex justify-content-between gap-2 mb-5">
                                <h4 class="mb-0 d-flex gap-1 text-title fz-16">
                                    {{ translate('Google_Analytics') }}
                                </h4>
                                <div class="custom--switch">
                                    <input type="checkbox" name="status" value="" id="google_analytics"
                                        switch="primary" class="toggle-switch-input">
                                    <label for="google_analytics" data-on-label="on" data-off-label="off"></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label
                                    class="text-title d-flex font-weight-medium">{{ translate('Measurement ID') }}</label>
                                <input type="hidden" name="type" value="">
                                <textarea type="text" placeholder="{{ translate('Enter Measurement ID') }}" class="form-control h--45px"
                                    name="value"></textarea>
                            </div>
                            <div class="d-flex gap-3 justify-content-between align-items-center">
                                <a class="font-weight-medium cursor-pointer" data-toggle="modal"
                                    data-target="#singleInstructionModal">
                                    <i class="tio-help-outlined"></i>
                                    {{ translate('How_It_Work') }}
                                </a>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary px-4 call-demo">{{translate('submit')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModal" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i
                            class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0 text-center">
                    <div class="row g-2 g-sm-3 mt-lg-0">
                        <div class="col-12">
                            <div class="swiper mySwiper pb-3">
                                <div class="swiper-wrapper">
                                    <div class="swiper-slide">
                                        <div class="d-flex flex-column align-items-center mx-w450 mx-auto">
                                            <img src="{{ asset('public/assets/admin/img/marketing-tools.png') }}"
                                                width="80" loading="lazy" alt=""
                                                class="dark-support rounded mb-4">
                                            <h3 class="mb-4">
                                                {{ translate('Marketing Tools') }}
                                            </h3>
                                            <ol class="text-left">
                                                <li class="mb-1">
                                                    {{ translate('Lorem Ipsum is simply dummy text of the printing and typesetting industry.') }}
                                                </li>
                                                <li class="mb-1">
                                                    {{ translate('Lorem Ipsum is the printing and typesetting industry.') }}
                                                </li>
                                                <li class="mb-1">
                                                    {{ translate('Lorem Ipsum is simply dummy text industry.') }}
                                                </li>
                                                <li class="mb-1">
                                                    {{ translate('Lorem Ipsum is simply dummy text of the printing industry.') }}
                                                </li>
                                                <li class="mb-1">{{ translate('Lorem Ipsum is simply dummy text.') }}
                                                </li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="singleInstructionModal" tabindex="-1" aria-labelledby="singleInstructionModal"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered max-w-655px">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0 pt-2 px-2 d-flex justify-content-end">
                    <button type="button" class="bg-transparent border-0 btn-close fz-22 p-0 text-black"
                        data-dismiss="modal" aria-label="Close"><i class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="swiper instruction-carousel pb-3">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="">
                                    <div class="d-flex justify-content-center mb-5">
                                        <img width="80"
                                            src="{{ asset('public/assets/admin/img/facebook-circle.png') }}"
                                            loading="lazy" alt="img">
                                    </div>
                                    <div class="text-start title-color mb-3">
                                        <h4 class="lh-md font-weight-bolder fz-16">
                                            {{ translate(' How to Get the Tag ID') }}
                                        </h4>
                                        <p class="">
                                            https://business.facebook.com/ > All tools > Events Manager > Data Sources >
                                            Select the Meta Pixel you want to connect > Copy Pixel ID (numeric only) >
                                            Paste the ID to the Meta Pixel field in your eFood Marketing Tool Page.
                                        </p>
                                    </div>
                                    <div class="text-start title-color mb-3">
                                        <h4 class="lh-md font-weight-bolder fz-16">
                                            {{ translate('Where to use the Tag ID') }}
                                        </h4>
                                        <p class="">Now go the <a href="javascript:">Firebase Console</a>
                                            And follow the instructions
                                            below -</p>
                                        <ol class="d-flex flex-column gap-2 title-color ">
                                            <li> {{ translate('Go to your Firebase project.') }}
                                            </li>
                                            <li> {{ translate('Navigate to the Build menu from the left sidebar and select Authentication.') }}
                                            </li>
                                            <li> {{ translate('Get started the project and go to the Sign-in method tab.') }}
                                            </li>
                                            <li> {{ translate('From the Sign-in providers section, select the Phone option.') }}
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="text-start title-color mb-3">
                                    <h4 class="lh-md font-weight-bolder fz-16">
                                        {{ translate('Where to use the Tag ID') }}
                                    </h4>
                                    <p class="">Now go the <a href="javascript:">Firebase Console</a>
                                        And follow the instructions
                                        below -</p>
                                    <ol class="d-flex flex-column gap-2 title-color ">
                                        <li> {{ translate('Go to your Firebase project.') }}
                                        </li>
                                        <li> {{ translate('Navigate to the Build menu from the left sidebar and select Authentication.') }}
                                        </li>
                                        <li> {{ translate('Get started the project and go to the Sign-in method tab.') }}
                                        </li>
                                        <li> {{ translate('From the Sign-in providers section, select the Phone option.') }}
                                        </li>
                                    </ol>
                                </div>
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <button class="btn btn--primary px-10 mt-3 text-capitalize"
                                        data-dismiss="modal">{{ translate('got_it') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <div class="instruction-pagination-custom"></div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script href="{{ asset('public/assets/admin/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script>
        $(window).on("load", function() {
            if ($(".instruction-carousel").length) {
                let slideCount = $(".instruction-carousel .swiper-slide").length;
                let swiperPaginationCustom = $(".instruction-pagination-custom");
                swiperPaginationCustom.html(`1 / ${slideCount}`);

                var swiper = new Swiper(".instruction-carousel", {
                    autoHeight: true,
                    pagination: {
                        el: ".swiper-pagination",
                        clickable: true,
                    },
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                    on: {
                        slideChange: () => {
                            swiperPaginationCustom.html(
                                `${swiper.realIndex + 1} / ${swiper.slides.length}`
                            );

                            if (swiper.isEnd) {
                                $(".instruction-pagination-custom, .swiper-pagination").css("display",
                                    "none");
                            } else {
                                $(".instruction-pagination-custom, .swiper-pagination").css("display",
                                    "block");
                            }
                        },
                    },
                });
            }
        });
    </script>
@endpush
