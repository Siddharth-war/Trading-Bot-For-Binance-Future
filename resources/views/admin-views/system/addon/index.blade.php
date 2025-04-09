@extends('layouts.admin.app')

@section('title', translate('System Addon'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('public/assets/admin/vendor/swiper/swiper-bundle.min.css')}}" />
    <style>
        .display-none{
            display: none!important;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/app.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('System Addon')}}
                </span>
            </h2>
            <div class="text-primary d-flex align-items-center gap-3 font-weight-bolder" data-toggle="modal" data-target="#settingModal">
                {{ translate('How the Setting Works') }}
                <div class="ripple-animation" >
                    <img src="{{asset('/public/assets/admin/img/info.svg')}}" class="svg" alt="">
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-body pl-md-10">
                <h4 class="mb-3 text-capitalize d-flex align-items-center">{{translate('upload_addon')}}</h4>
                <form enctype="multipart/form-data" id="theme_form">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-3">
                            <div class="uploadDnD">
                                <div class="form-group inputDnD mb-3">
                                    <input type="file" name="file_upload" class="form-control-file text--primary font-weight-bold"
                                           id="inputFile" onchange="readUrl(this)" accept=".zip" data-title="Drag & drop file or Browse file">
                                </div>
                            </div>

                            <div class="mt-5 card px-3 py-2 d--none" id="progress-bar">
                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <div class="">
                                        <img width="24" src="{{asset('/public/assets/admin/img/zip.png')}}" alt="">
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span id="name_of_file" class="text-truncate fz-12"></span>
                                            <span class="text-muted fz-12" id="progress-label">0%</span>
                                        </div>
                                        <progress id="uploadProgress" class="w-100" value="0" max="100"></progress>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php($condition_one=str_replace('MB','',ini_get('upload_max_filesize'))>=20 && str_replace('MB','',ini_get('upload_max_filesize'))>=20)
                        @php($condition_two=str_replace('MB','',ini_get('post_max_size'))>=20 && str_replace('MB','',ini_get('post_max_size'))>=20)

                        <div class="col-sm-6 col-lg-5 col-xl-4 col-xxl-9">
                            <div class="pl-sm-5">
                                <h5 class="mb-3 d-flex">{{ translate('instructions') }}</h5>
                                <ul class=" d-flex flex-column gap-2 instructions-list list-unstyled">
                                    <li>
                                        1. {{ translate('please_make_sure') }}, {{ translate('your_server_php') }}
                                        "upload_max_filesize" {{translate('value_is_grater
                                   _or_equal_to_20MB') }}. {{ translate('current_value_is') }}
                                        - {{ini_get('upload_max_filesize')}}B
                                    </li>
                                    <li>
                                        2. {{ translate('please_make_sure')}}, {{ translate('your_server_php')}}
                                        "post_max_size"
                                        {{translate('value_is_grater_or_equal_to_20MB')}}
                                        . {{translate('current_value_is') }} - {{ini_get('post_max_size')}}B
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-end mt-3">
                                <button type="button"
                                        class="btn btn-primary px-4" id="upload_theme">{{translate('upload')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-1 g-sm-2">
            @foreach($addons as $key => $addon)
                @php($data= include $addon.'/Addon/info.php')
                <div class="col-6 col-md-4 col-xxl-3">
                    <div class="card theme-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{$data['name']}}
                            </h3>

                            <div class="d-flex gap-2 gap-sm-3 align-items-center">
                                @if ($data['is_published'] == 0)
                                    <button class="text-danger bg-transparent p-0 border-0 mr-2" data-toggle="modal" data-target="#deleteThemeModal_{{$key}}"><img src="{{asset('public/assets/admin/img/delete.svg')}}" class="svg" alt=""></button>
                                    <div class="modal fade" id="deleteThemeModal_{{$key}}" tabindex="-1" aria-labelledby="deleteThemeModal_{{$key}}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                                                    <button
                                                        type="button"
                                                        class="btn-close border-0"
                                                        data-dismiss="modal"
                                                        aria-label="Close"
                                                    ><i class="tio-clear"></i></button>
                                                </div>
                                                <div class="modal-body px-4 px-sm-5 text-center">
                                                    <div class="mb-3 text-center">
                                                        <img width="75" src="{{asset('public/assets/admin/img/delete.png')}}" alt="">
                                                    </div>

                                                    <h3>{{ translate('are_you_sure_you_want_to_delete_the'.' ') }} {{ $data['name'] }}?</h3>
                                                    <p class="mb-5">{{ translate('once_you_delete') }}, {{ translate('you_will_lost_the_this'.' ') }} {{ $data['name'] }}</p>
                                                    <div class="d-flex justify-content-center gap-3 mb-3">
                                                        <button type="button" class="fs-16 btn btn-secondary px-sm-5" data-dismiss="modal">{{ translate('cancel') }}</button>
                                                        <button type="submit" class="fs-16 btn btn-danger px-sm-5" data-dismiss="modal" id="deleteButton" data-addon="{{$addon}}">{{ translate('delete') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <button class="{{$data['is_published'] == 1 ? 'checkbox-color-primary' : 'text-muted'}} bg-transparent p-0 border-0" data-toggle="modal" data-target="#shiftThemeModal_{{$key}}"><img src="{{asset('public/assets/admin/img/check.svg')}}" class="svg" alt=""></button>

                                <div class="modal fade" id="shiftThemeModal_{{$key}}" tabindex="-1" aria-labelledby="shiftThemeModalLabel_{{$key}}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                                                <button
                                                    type="button"
                                                    class="btn-close border-0"
                                                    data-dismiss="modal"
                                                    aria-label="Close"
                                                ><i class="tio-clear"></i></button>
                                            </div>
                                            <div class="modal-body px-4 px-sm-5 text-center">
                                                <div class="mb-3 text-center">
                                                    <img width="75" src="{{asset('public/assets/admin/img/shift.png')}}" alt="">
                                                </div>

                                                <h3>{{ translate('are_you_sure?') }}</h3>
                                                @if ($data['is_published'])
                                                    <p class="mb-5">{{ translate('want_to_inactive_this'.' ') }} {{ $data['name'] }}</p>
                                                @else
                                                    <p class="mb-5">{{ translate('want_to_activate_this'.' ') }} {{ $data['name'] }}</p>
                                                @endif
                                                <div class="d-flex justify-content-center gap-3 mb-3">
                                                    <button type="button" class="fs-16 btn btn-secondary px-sm-5" data-dismiss="modal">{{ translate('no') }}</button>
                                                    <button type="button" class="fs-16 btn btn-primary px-sm-5" data-dismiss="modal" id="activeBtn" data-addon="{{$addon}}">{{ translate('yes') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-2 p-sm-3">
                            <div class="mb-2 display-none" id="activate_{{$key}}">
                                <form action="" method="post">
                                    @csrf
                                    <div class="form-group mb-2">
                                        <input type="text" name="username" value=""
                                               class="form-control" placeholder="{{ translate('codecanyon_username') }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <input type="text" name="purchase_code" value=""
                                               class="form-control" placeholder="{{ translate('purchase_code') }}">
                                        <input type="text" name="path" class="form-control" value="" hidden>
                                    </div>

                                    <div>
                                        <input type="hidden" value="key" name="theme">
                                        <button type="submit" class="btn btn-primary radius-button text-end">{{translate('activate')}}</button>
                                    </div>
                                </form>
                            </div>

                            <div class="aspect-ration-3:2 border border-color-primary-light radius-10">
                                <img class="img-fit radius-10"
                                     onerror='this.src="{{asset('public/assets/admin/img/placeholder.png')}}"'
                                     src="{{asset($addon.'/public/addon.png')}}">
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @include('admin-views.system.addon.partials.activation-modal')
        </div>

        <div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModal" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                        <button
                            type="button"
                            class="btn-close border-0"
                            data-dismiss="modal"
                            aria-label="Close"
                        ><i class="tio-clear"></i></button>
                    </div>
                    <div class="modal-body px-4 px-sm-5 pt-0 text-center">
                        <div class="row g-2 g-sm-3 mt-lg-0">
                            <div class="col-12">
                                <div class="swiper mySwiper pb-3">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="d-flex flex-column align-items-center mx-w450 mx-auto">
                                                <img src="{{asset('public/assets/admin/img/addon_setting.png')}}" loading="lazy"
                                                     alt="" class="dark-support rounded mb-4">
                                                <h3 class="mb-4">{{translate('To Integrate add-on to your system please follow the instruction below')}}</h3>

                                                <ol class="text-left">
                                                    <li>{{translate('After purchasing Payment & SMS Module from codecanyon. You will find a file download option.')}}</li>
                                                    <li>{{translate('Download the file. It will be downloaded as Zip format (Filename.Zip)')}}</li>
                                                    <li>{{translate('Extract the file you will get a file name payment.zip.')}}</li>
                                                    <li>{{translate('Upload the file here and your Addon uploading is complete !')}}</li>
                                                    <li>{{translate('Then active the Addon and setup all the options. you are good to go !')}}</li>
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
    </div>
@endsection

@push('script_2')
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script href="{{ asset('public/assets/admin/vendor/swiper/swiper-bundle.min.js')}}"></script>

    <script>
        $("img.svg").each(function () {
            var $img = jQuery(this);
            var imgID = $img.attr("id");
            var imgClass = $img.attr("class");
            var imgURL = $img.attr("src");

            jQuery.get(
                imgURL,
                function (data) {
                    var $svg = jQuery(data).find("svg");

                    if (typeof imgID !== "undefined") {
                        $svg = $svg.attr("id", imgID);
                    }
                    if (typeof imgClass !== "undefined") {
                        $svg = $svg.attr("class", imgClass + " replaced-svg");
                    }
                    $svg = $svg.removeAttr("xmlns:a");
                    if (
                        !$svg.attr("viewBox") &&
                        $svg.attr("height") &&
                        $svg.attr("width")
                    ) {
                        $svg.attr(
                            "viewBox",
                            "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
                        );
                    }
                    $img.replaceWith($svg);
                },
                "xml"
            );
        });
    </script>

    <script>
        function readUrl(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = (e) => {
                    let imgData = e.target.result;
                    let imgName = input.files[0].name;
                    input.setAttribute("data-title", imgName);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <script>
        "use strict";

        $('#upload_theme').on('click', function() {
            if ("{{env('APP_MODE')}}" != 'demo') {
                zip_upload();
            } else {
                call_demo();
            }
        });

        $('#deleteButton').on('click', function() {
            var addon = $(this).data('addon');
            theme_delete(addon);
        });

        $('#activeBtn').on('click', function() {
            var addon = $(this).data('addon');
            publish_addon(addon);
        });

        function zip_upload(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var formData = new FormData(document.getElementById('theme_form'));
            $.ajax({
                type: 'POST',
                url: "{{route('admin.system-addon.upload')}}",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    $('#progress-bar').show();

                    xhr.upload.addEventListener("progress", function(e) {
                        if (e.lengthComputable) {
                            var percentage = Math.round((e.loaded * 100) / e.total);
                            $("#uploadProgress").val(percentage);
                            $("#progress-label").text(percentage + "%");
                        }
                    }, false);

                    return xhr;
                },
                beforeSend: function () {
                    $('#upload_theme').attr('disabled');
                },
                success: function(response) {
                    if (response.status == 'error') {
                        $('#progress-bar').hide();
                        toastr.error(response.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(response.status == 'success'){
                        toastr.success(response.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        location.reload();
                    }
                },
                complete: function () {
                    $('#upload_theme').removeAttr('disabled');
                },
            });
        }

        function publish_addon(path) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.system-addon.publish')}}',
                data: {
                    'path': path
                },
                success: function (data) {
                    if (data.flag === 'inactive') {
                        $('#activatedThemeModal').modal('show');
                        $('#activateData').empty().html(data.view);
                    } else {
                        if (data.errors) {
                            for (var i = 0; i < data.errors.length; i++) {
                                toastr.error(data.errors[i].message, {
                                    CloseButton: true,
                                    ProgressBar: true
                                });
                            }
                        } else {
                            toastr.success('{{ translate("updated successfully!") }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            setTimeout(function () {
                                location.reload()
                            }, 2000);
                        }
                    }
                }
            });
        }

        function theme_delete(path){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.system-addon.delete')}}',
                data: {
                    path
                },
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    if (data.status === 'success') {
                        setTimeout(function () {
                            location.reload()
                        }, 2000);

                        toastr.success(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }else if(data.status === 'error'){
                        toastr.error(data.message, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    }
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        }

        let swiper = new Swiper(".mySwiper", {
            pagination: {
                el: ".swiper-pagination",
                dynamicBullets: true,
            },
        });
    </script>
@endpush
