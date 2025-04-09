@extends('layouts.admin.app')

@section('title', translate('email_template'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="">
            <div class="d-flex flex-wrap justify-content-between align-items-center __gap-15px">
                <h1 class="page-header-title mr-3 mb-0">
                    <span class="page-header-icon">
                        <img src="{{ asset('public/assets/admin/img/email-setting.png') }}" class="w--26" alt="">
                    </span>
                    <span class="ml-2">
                        {{ translate('Email_Templates') }}
                    </span>
                </h1>
                @include('admin-views.business-settings.email-format-setting.partials.email-template-options')
            </div>
            @include('admin-views.business-settings.email-format-setting.partials.user-email-template-setting-links')
        </div>
        <div class="tab-content">
            <div class="tab-pane fade show active">
                <div class="card mb-3">
                    @php($mail_status=\App\Model\BusinessSetting::where('key','place_order_mail_status_user')->first())
                    @php($mail_status = $mail_status ? $mail_status->value : '0')
                    <div class="card-body">
                        <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                            <h5 class="text-capitalize m-0 text--primary pl-2">
                                {{translate('Send_Mail_On_Order_Placement')}}
                                <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Customers_will_receive_an_automated_email_after_a_successful_order_placement.')}}">
                                    <img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('show_hide_food_menu') }}">
                                 </span>
                            </h5>
                            <label class="toggle-switch toggle-switch-sm">
                                <input type="checkbox" class="status toggle-switch-input" onclick="toogleStatusModal(event,'mail-status','place-order-on.png','place-order-off.png','{{translate('Want_to_enable_the')}} <strong>{{translate('Order_Placement')}}</strong> {{translate('mail')}} ?','{{translate('Want_to_disable_the')}} <strong>{{translate('Order_Placement')}}</strong> {{translate('mail')}} ?',`<p>{{translate('If_enabled,_customers_will_get_an_automatic_confirmation_mail_for_successful_Order_Placement_with_an_invoice.')}}</p>`,`<p>{{translate('If_disabled,_customers_will_NOT_get_any_Order_Placement_email.')}}</p>`)" id="mail-status" {{$mail_status == '1'?'checked':''}}>
                                <span class="toggle-switch-label text mb-0">
                                    <span class="toggle-switch-indicator"></span>
                                </span>
                            </label>
                        </div>
                        <form action="{{route('admin.business-settings.email-status',['user','place-order',$mail_status == '1'?0:1])}}" method="get" id="mail-status_form"></form>
                    </div>
                </div>
                @php($data=\App\Models\EmailTemplate::where('type','user')->where('email_type', 'new_order')->first())
                @php($template=$template?$template:($data?$data->email_template:3))
                <form action="{{ route('admin.business-settings.email-setup.update', ['user','new-order']) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card border-0">
                        <div class="card-body">
                            <div class="email-format-wrapper">
                                <div class="left-content">
                                    <div class="d-inline-block">
                                        @include('admin-views.business-settings.email-format-setting.partials.email-template-section')
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            @include('admin-views.business-settings.email-format-setting.templates.email-format-'.$template)
                                        </div>
                                    </div>
                                </div>
                                <div class="right-content">
                                    <div class="d-flex flex-wrap justify-content-between __gap-15px mt-2 mb-5">
                                        @php($data=\App\Models\EmailTemplate::withoutGlobalScope('translate')->with('translations')->where('type','user')->where('email_type', 'new_order')->first())

                                        @php($language=\App\Model\BusinessSetting::where('key','language')->first())
                                        @php($language = $language->value ?? null)
                                        @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                        @if($language)
                                            <ul class="nav nav-tabs m-0 border-0">
                                                <li class="nav-item">
                                                    <a class="nav-link lang_link active"
                                                    href="#"
                                                    id="default-link">{{translate('default')}}</a>
                                                </li>
                                                @foreach (json_decode($language) as $lang)
                                                    <li class="nav-item">
                                                        <a class="nav-link lang_link"
                                                            href="#"
                                                            id="{{ $lang->code }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang->code) . '(' . strtoupper($lang->code) . ')' }}</a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <div class="d-flex justify-content-end">
                                            <div class="text-primary py-1 d-flex flex-wrap align-items-center py-1" type="button" data-toggle="modal" data-target="#instructions">
                                                <strong class="mr-2">{{translate('Read_Instructions')}}</strong>
                                                <div class="blinkings">
                                                    <i class="tio-info-outined"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            {{translate('Logo')}}   <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Logo_must_be_1:1')}}">
                                                <img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('show_hide_food_menu') }}">
                                            </span>
                                        </h5>
                                        <label class="custom-file">
                                            <input type="file" name="logo" id="mail-logo" class="custom-file-input" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <span class="custom-file-label">{{ translate('Choose_File') }}</span>
                                        </label>
                                    </div>
                                    <br>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            <img src="{{asset('public/assets/admin/img/pointer.png')}}" class="mr-2" alt="">
                                            {{translate('Header_Content')}}
                                        </h5>
                                        @if ($language)
                                            <div class="__bg-F8F9FC-card default-form lang_form" id="default-form">
                                                <div class="form-group">
                                                    <label class="form-label">{{translate('Main_Title')}}({{ translate('default') }})
                                                        <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_main_title_within_45_characters')}}">
                                                            <img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('show_hide_food_menu') }}">
                                                        </span>
                                                    </label>
                                                    <input type="text" maxlength="45" name="title[]" value="{{ $data?->getRawOriginal('title') }}" data-id="mail-title" placeholder="{{ translate('Order_has_been_placed_successfully.') }}" class="form-control">
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="form-label">
                                                        {{ translate('Mail_Body_Message') }}({{ translate('default') }})
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_mail_body_message_within_75_words')}}">
                                                            <i class="tio-info-outined"></i>
                                                        </span>
                                                    </label>
                                                    <textarea class="form-control" id="ckeditor" data-id="mail-body" name="body[]">
                                                        {!! $data?->getRawOriginal('body') !!}
                                                    </textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                            @foreach(json_decode($language) as $lang)
                                            <?php
                                            $lang_code = $lang->code;
                                            if($data && count($data['translations'])){
                                                $translate = [];
                                                foreach($data['translations'] as $t)
                                                {
                                                    if($t->locale == $lang_code && $t->key=="title"){
                                                        $translate[$lang_code]['title'] = $t->value;
                                                    }
                                                    if($t->locale == $lang_code && $t->key=="body"){
                                                        $translate[$lang_code]['body'] = $t->value;
                                                    }
                                                }
                                            }
                                                ?>

                                                <div class="__bg-F8F9FC-card d-none lang_form" id="{{$lang->code}}-form">
                                                    <div class="form-group">
                                                       <label class="form-label">{{translate('Main_Title')}}({{strtoupper($lang->code)}})
                                                            <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_45_characters')}}">
                                                                <img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('show_hide_food_menu') }}">
                                                            </span>
                                                        </label>
                                                        <input type="text" maxlength="45" name="title[]"  placeholder="{{ translate('Order_has_been_placed_successfully.') }}" class="form-control" value="{{$translate[$lang->code]['title']??''}}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                       <label class="form-label">
                                                            {{ translate('Mail_Body_Message') }}({{strtoupper($lang->code)}})
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_mail_body_message_within_75_words')}}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                        </label>
                                                        <textarea class="ckeditor form-control" name="body[]">
                                                           {!! $translate[$lang->code]['body']??'' !!}
                                                        </textarea>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="lang[]" value="{{$lang->code}}">
                                            @endforeach
                                        @else
                                            <div class="__bg-F8F9FC-card default-form">
                                                <div class="form-group">
                                                    <label class="form-label">{{translate('Main_Title')}}
                                                    <span class="input-label-secondary" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_title_within_45_characters')}}">
                                                                <img src="{{ asset('/public/assets/admin/img/info-circle.svg') }}" alt="{{ translate('show_hide_food_menu') }}">
                                                            </span></label>
                                                    <input type="text" maxlength="45" name="title[]" placeholder="{{ translate('Order_has_been_placed_successfully.') }}"class="form-control">
                                                </div>
                                                <div class="form-group mb-0">
                                                      <label class="form-label">
                                                        {{ translate('Mail_Body_Message') }}
                                                         <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_mail_body_message_within_75_words')}}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                    </label>
                                                    <textarea class="ckeditor form-control" name="body[]">
                                                        Hi Sabrina,
                                                    </textarea>
                                                </div>
                                            </div>
                                            <input type="hidden" name="lang[]" value="default">
                                        @endif

                                    </div>
                                    <br>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            <img src="{{asset('public/assets/admin/img/pointer.png')}}" class="mr-2" alt="">
                                            {{translate('Button_Content')}}
                                        </h5>
                                        <div class="__bg-F8F9FC-card">
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    @if ($language)
                                                        <div class="form-group m-0 lang_form default-form">
                                                            <label class="form-label text-capitalize">
                                                                {{translate('Button_Name')}}({{ translate('default') }})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_button_name_within_15_characters.') }}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" maxlength="15" data-id="mail-button" name="button_name[]"  placeholder="{{translate('Ex:_Order_now')}}" class="form-control h--45px" value="{{ $data?->getRawOriginal('button_name') }}">
                                                        </div>
                                                    @foreach(json_decode($language) as $lang)
                                                    <?php
                                                    $lang_code = $lang->code;
                                                    if($data && count($data['translations'])){
                                                        $translate = [];
                                                        foreach($data['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang_code && $t->key=="button_name"){
                                                                $translate[$lang_code]['button_name'] = $t->value;
                                                            }
                                                        }
                                                        }
                                                        ?>
                                                        <div class="form-group m-0 d-none lang_form" id="{{$lang->code}}-form1">
                                                            <label class="form-label text-capitalize">
                                                                {{translate('Button_Name')}}({{strtoupper($lang->code)}})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_button_name_within_15_characters.') }}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" maxlength="15" name="button_name[]"  placeholder="{{translate('Ex:_Order_now')}}" class="form-control h--45px" value="{{ $translate[$lang->code]['button_name']??'' }}">
                                                        </div>
                                                    @endforeach
                                                @else
                                                <div class="form-group m-0">
                                                     <label class="form-label text-capitalize">
                                                        {{translate('Button_Name')}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_button_name_within_15_characters.') }}">
                                                            <i class="tio-info-outined"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text" maxlength="15" placeholder="{{translate('Ex:_Order_now')}}" class="form-control h--45px" name="button_name[]" value="">
                                                </div>
                                                @endif
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group m-0">
                                                          <label class="form-label">
                                                            {{translate('Redirect_Link')}}
                                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Link_to_your_preferred_destination_that_will_work_when_someone_clicks_on_the_Button_Name._Add_the_link_where_the_button_will_redirect_users.') }}">
                                                                <i class="tio-info-outined"></i>
                                                            </span>
                                                        </label>
                                                        <input type="url" name="button_url" placeholder="{{ translate('Please_contact_us_for_any_queries_we_are_always_happy_to_help') }}"  class="form-control" value="{{ $data['button_url']??'' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div>
                                        <h5 class="card-title mb-3">
                                            <img src="{{asset('public/assets/admin/img/pointer.png')}}" class="mr-2" alt="">
                                            {{translate('Footer_Content')}}
                                        </h5>
                                        <div class="__bg-F8F9FC-card">
                                                @if ($language)
                                                        <div class="form-group lang_form default-form">
                                                            <label class="form-label">
                                                                {{translate('Section_Text')}}({{ translate('default') }})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_footer_text_within_75_characters')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" maxlength="75" data-id="mail-footer" name="footer_text[]"  placeholder="{{ translate('Please_contact_us_for_any_queries_we_are_always_happy_to_help') }}"  class="form-control" value="{{ $data?->getRawOriginal('footer_text') }}">
                                                        </div>
                                                    @foreach(json_decode($language) as $lang)
                                                    <?php
                                                        $lang_code = $lang->code;
                                                    if($data && count($data['translations'])){
                                                        $translate = [];
                                                        foreach($data['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang_code && $t->key=="footer_text"){
                                                                $translate[$lang_code]['footer_text'] = $t->value;
                                                            }
                                                        }
                                                        }
                                                        ?>
                                                        <div class="form-group d-none lang_form" id="{{$lang->code}}-form2">
                                                           <label class="form-label">
                                                                {{translate('Section_Text')}}({{strtoupper($lang->code)}})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_footer_text_within_75_characters')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" maxlength="75" name="footer_text[]"  placeholder="{{ translate('Please_contact_us_for_any_queries_we_are_always_happy_to_help') }}"  class="form-control" value="{{ $translate[$lang->code]['footer_text']??'' }}">
                                                        </div>
                                                    @endforeach
                                                @else
                                                <div class="form-group">
                                                  <label class="form-label">
                                                        {{translate('Section_Text')}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_footer_text_within_75_characters')}}">
                                                            <i class="tio-info-outined"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text"  maxlength="75" placeholder="{{ translate('Please_contact_us_for_any_queries_we_are_always_happy_to_help') }}"  class="form-control" name="footer_text[]" value="">
                                                </div>
                                                @endif
                                            <div class="form-group">
                                                <label class="form-label">
                                                    {{translate('Page_Links')}}
                                                </label>
                                                <ul class="page-links-checkgrp">
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input privacy-check" onchange="checkMailElement('privacy-check')" type="checkbox" name="privacy" value ="1" {{ (isset($data['privacy']) && $data['privacy'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Privacy_Policy')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input refund-check" onchange="checkMailElement('refund-check')" type="checkbox" name="refund" value ="1" {{ (isset($data['refund']) && $data['refund'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Refund_Policy')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input cancelation-check" onchange="checkMailElement('cancelation-check')" type="checkbox" name="cancelation" value ="1" {{ (isset($data['cancelation']) && $data['cancelation'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Cancelation_Policy')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input contact-check" onchange="checkMailElement('contact-check')" type="checkbox" name="contact" value ="1" {{ (isset($data['contact']) && $data['contact'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Contact_Us')}}</span>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">
                                                    {{translate('Social_Media_Links')}}
                                                </label>
                                                <ul class="page-links-checkgrp">
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input facebook-check" type="checkbox" onchange="checkMailElement('facebook-check')" name="facebook" value="1" {{ (isset($data['facebook']) && $data['facebook'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Facebook')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input instagram-check" type="checkbox" onchange="checkMailElement('instagram-check')" name="instagram" value="1" {{ (isset($data['instagram']) && $data['instagram'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Instagram')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input twitter-check" type="checkbox" onchange="checkMailElement('twitter-check')" name="twitter" value="1" {{ (isset($data['twitter']) && $data['twitter'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Twitter')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input linkedin-check" type="checkbox" onchange="checkMailElement('linkedin-check')" name="linkedin" value="1" {{ (isset($data['linkedin']) && $data['linkedin'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('LinkedIn')}}</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="form-check form--check">
                                                            <input class="form-check-input pinterest-check" type="checkbox" onchange="checkMailElement('pinterest-check')" name="pinterest" value="1" {{ (isset($data['pinterest']) && $data['pinterest'] == 1)?'checked':'' }}>
                                                            <span class="form-check-label">{{translate('Pinterest')}}</span>
                                                        </label>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="form-group mb-0">
                                                @if ($language)
                                                       <div class="form-group lang_form default-form">
                                                            <label class="form-label">
                                                                {{translate('Copyright_Content')}}({{ translate('default') }})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_Copyright_Content_within_50_characters')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" maxlength="50" data-id="mail-copyright" name="copyright_text[]"  placeholder="{{ translate('Ex:_Copyright_2023_eFood._All_right_reserved')}}" class="form-control" value="{{ $data?->getRawOriginal('copyright_text') }}">
                                                        </div>
                                                    @foreach(json_decode($language) as $lang)
                                                    <?php
                                           $translate = [];
                                           if($data && count($data['translations'])){
                                                        foreach($data['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang->code && $t->key=="copyright_text"){
                                                                $translate[$lang->code]['copyright_text'] = $t->value;
                                                            }
                                                        }
                                                        }
                                                        ?>
                                                        <div class="form-group d-none lang_form" id="{{$lang->code}}-form3">
                                                            <label class="form-label">
                                                                {{translate('Copyright_Content')}}({{strtoupper($lang->code)}})
                                                                <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_Copyright_Content_within_50_characters')}}">
                                                                    <i class="tio-info-outined"></i>
                                                                </span>
                                                            </label>
                                                            <input type="text" maxlength="50" name="copyright_text[]"  placeholder="{{ translate('Ex:_Copyright_2023_eFood._All_right_reserved')}}" class="form-control" value="{{ $translate[$lang->code]['copyright_text']??'' }}">
                                                        </div>
                                                    @endforeach
                                                @else
                                                <div class="form-group">
                                                     <label class="form-label">
                                                        {{translate('Copyright_Content')}}
                                                        <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title="{{ translate('Write_the_Copyright_Content_within_50_characters')}}">
                                                            <i class="tio-info-outined"></i>
                                                        </span>
                                                    </label>
                                                    <input type="text" maxlength="50"  placeholder="{{ translate('Ex:_Copyright_2023_eFood._All_right_reserved')}}"class="form-control" name="copyright_text[]" value="">
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn--container justify-content-end mt-3">
                                        <button type="reset" id="reset_btn" class="btn btn-secondary">{{translate('Reset')}}</button>
                                        <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>


        <!-- Instructions Modal -->
@include('admin-views.business-settings.email-format-setting.partials.email-template-instructions')

    </div>

@endsection

@push('script_2')
    <script src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
    <script>
        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.substring(0, form_id.length - 5);

            console.log(lang);

            $("#"+lang+"-form").removeClass('d-none');
            $("#"+lang+"-form1").removeClass('d-none');
            $("#"+lang+"-form2").removeClass('d-none');
            $("#"+lang+"-form3").removeClass('d-none');
            if(lang == '{{$default_lang}}')
            {
                $(".from_part_2").removeClass('d-none');
            }
            if(lang == 'default')
            {
                $(".default-form").removeClass('d-none');
            }
            else
            {
                $(".from_part_2").addClass('d-none');
            }
        });
    </script>
    <script>

        var editor = CKEDITOR.replace('ckeditor');

        editor.on( 'change', function( evt ) {
            $('#mail-body').empty().html(evt.editor.getData());
        });

        $('input[data-id="mail-title"]').on('keyup', function() {
            var dataId = $(this).data('id');
            var value = $(this).val();
            $('#'+dataId).text(value);
        });
        $('input[data-id="mail-button"]').on('keyup', function() {
            var dataId = $(this).data('id');
            var value = $(this).val();
            $('#'+dataId).text(value);
        });
        $('input[data-id="mail-footer"]').on('keyup', function() {
            var dataId = $(this).data('id');
            var value = $(this).val();
            $('#'+dataId).text(value);
        });
        $('input[data-id="mail-copyright"]').on('keyup', function() {
            var dataId = $(this).data('id');
            var value = $(this).val();
            $('#'+dataId).text(value);
        });

        function readURL(input, viewer) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + viewer).attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#mail-logo").change(function() {
            readURL(this, 'logoViewer');
        });

        $("#mail-banner").change(function() {
            readURL(this, 'bannerViewer');
        });

        $("#mail-icon").change(function() {
            readURL(this, 'iconViewer');
        });


    </script>

@endpush
