@extends('layouts.admin.app')

@section('title', translate('Messages'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/lightbox.css')}}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/message.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Messages')}} <span id="message-count" class="badge badge-soft-dark rounded-50 fz-12 ml-1"></span>
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="py-4" id="conversation_sidebar">
                        <div class="chat_people media px-3 gap-3 mb-4">
                            <div class="avatar position-relative">
                                <img src="{{auth('admin')->user()->imageFullPath}}" class="img-fit rounded-circle" alt="{{ translate('admin') }}">
                                <span class="avatar-status status-sm bg-success"></span>
                            </div>
                            <div class="chat_ib media-body">
                                <h5 class="mb-0">{{auth('admin')->user()->f_name}} {{auth('admin')->user()->l_name}}</h5>
                                <span class="fz-12">{{auth('admin')->user()->phone}}</span>
                            </div>
                        </div>

                        <div class="px-3 mb-3">
                            <div class="chat_search">
                                <i class="tio-search"></i>
                                <input placeholder="{{translate('Search customers...')}}"
                                    class="cz-filter-search form-control"
                                    type="text" id="search-conversation-user" autocomplete="off">
                            </div>
                        </div>

                        <div class="customer-list-wrap">
                        @php($array=[])
                        @foreach($conversations as $conv)
                            @if(in_array($conv->user_id,$array)==false)
                                @php(array_push($array,$conv->user_id))
                                @php($user=\App\User::find($conv->user_id))
                                <?php if (!$user){
                                        \App\Model\Conversation::where('user_id', $conv->user_id)->update(['checked' => 1]);
                                    } ?>
                                @php($unchecked=\App\Model\Conversation::where(['user_id'=>$conv->user_id,'checked'=>0])->count())

                                @if(isset($user))
                                <div class="sidebar_primary_div d-flex justify-content-between align-items-center cursor-pointer customer-list view-convs {{$unchecked!=0?'conv-active':''}}"
                                     data-url="{{route('admin.message.view',[$conv->user_id])}}" data-id="customer-{{$conv->user_id}}"
                                    id="customer-{{$conv->user_id}}">
                                    <div class="media align-items-center gap-10">
                                        <div class="avatar avatar-sm avatar-circle">
                                            <img class="img-fit rounded-circle" src="{{$user['imageFullPath']}}" alt="{{ translate('customer') }}">
                                        </div>
                                        <div class="sidebar_name">
                                            <h5 class="mb-0 li-pointer">{{$user['f_name'].' '.$user['l_name']}}</h5>
                                            <div class="fz-12 li-pointer">{{ $user['phone'] }}</div>

                                        </div>
                                    </div>
                                    <span class="{{$unchecked!=0?'chat_count':''}}" id="counter-{{$conv->user_id}}">
                                        {{$unchecked!=0?$unchecked:''}}
                                    </span>

                                </div>
                                @endif
                            @endif
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card card-body mb-1 d-none">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modal-conversation-start">
                            <i class="tio-add"></i>
                            {{translate('start_conversation')}}
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="align-items-center w-100" id="view-conversation">
                        <div class="d-flex h-100 w-100 justify-content-center align-items-center">
                            <div>
                                <img src="{{asset('public/assets/admin/img/view-conv.png')}}" class="mw-100" alt="">
                            </div>

                        </div>
                        <div class="d-flex h-100 w-100 justify-content-center align-items-center">
                            {{translate('click from the customer list to view conversation')}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script_2')
    <script src="{{asset('public/assets/admin/js/lightbox.js')}}"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js"></script>
    <script>
        "use strict";

        $('.view-convs').click(function() {
            const url = $(this).data('url');
            const id = $(this).data('id');
            viewConvs(url, id);
        });

        $("#search-conversation-user").on("keyup", function () {
            var input_value = this.value.toLowerCase().trim();
            var i;
            let sidebar_primary_div = $(".sidebar_primary_div");
            let sidebar_name = $(".sidebar_name");

            for (i = 0; i < sidebar_primary_div.length; i++) {
                const text_value = sidebar_name[i].innerText;
                if (text_value.toLowerCase().indexOf(input_value) > -1) {
                    sidebar_primary_div[i].style.display = "";
                } else {
                    sidebar_primary_div[i].style.setProperty("display", "none", "important");
                }
            }
        });

        let current_selected_user = null;

        function viewConvs(url, id_to_active) {
            current_selected_user = id_to_active;
            var counter_element = $('#counter-'+ current_selected_user.slice(9));
            var customer_element = $('#'+current_selected_user);
            if(counter_element !== "undefined") {
                counter_element.empty();
                counter_element.removeClass("chat_count");
            }
            if(customer_element !== "undefined") {
                customer_element.removeClass("conv-active");
            }


            $('.customer-list').removeClass('conv-active');
            $('#' + id_to_active).addClass('conv-active');
            $.get({
                url: url,
                success: function (data) {
                    $('#view-conversation').html(data.view);
                }
            });
        }

        function replyConvs(url) {
            var form = document.querySelector('form');
            var formdata = new FormData(form);

            if (!formdata.get('reply') && !formdata.get('images[]')) {
                toastr.error('{{translate("Reply message is required!")}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
                return "false";
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: url,
                type: 'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function (data) {
                    toastr.success('Message sent', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                    $('#view-conversation').html(data.view);
                },
                error() {
                    toastr.error('{{translate("Reply message is required!")}}', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        }

        function renderUserList() {
            $('#loading').show();
            $.ajax({
                url: "{{route('admin.message.get_conversations')}}",
                type: 'GET',
                cache: false,
                success: function (response) {
                    $('#loading').hide();
                    $("#conversation_sidebar").html(response.conversation_sidebar)

                },
                error: function (err) {
                    $('#loading').hide();
                }
            });
        }

        @php($config=Helpers::get_business_settings('firebase_message_config'))
        firebase.initializeApp({
            apiKey: "{{ $config['apiKey'] ?? '' }}",
            authDomain: "{{ $config['authDomain'] ?? '' }}",
            projectId: "{{ $config['projectId'] ?? '' }}",
            storageBucket: "{{ $config['storageBucket'] ?? '' }}",
            messagingSenderId: "{{ $config['messagingSenderId'] ?? '' }}",
            appId: "{{ $config['appId'] ?? '' }}"
        });

        const messaging = firebase.messaging();

        //service worker registration
        if ('serviceWorker' in navigator) {
            var swRegistration = navigator.serviceWorker.register('{{ asset('firebase-messaging-sw.js') }}')
                .then(function (registration) {
                    getToken(registration);
                }).catch(function (err) {
                });
        }

        function getToken(registration) {
            messaging.requestPermission()
                .then(function () {
                    let token = messaging.getToken({serviceWorkerRegistration: registration});
                    return token;
                })
                .then(function (token) {
                    update_fcm_token(token);
                })
                .catch((err) => {
                });
        }


        messaging.onMessage(payload => {
            renderUserList();
            if (current_selected_user != null && current_selected_user.slice(9) === payload.notification.body) {
                document.getElementById(current_selected_user).onclick();
            } else {
                toastr.info(payload.notification.title ? payload.notification.title : 'New message arrived.');
            }

        });


        function update_fcm_token(token) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: "{{route('admin.message.update_fcm_token')}}",
                data: {
                    fcm_token: token,
                },
                cache: false,
                success: function (data) {
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    toastr.error('{{translate("FCM token updated failed")}}');
                }
            });
        }

        let count = $(".sidebar_primary_div").length
        $("#message-count").text(count)

    </script>

@endpush
