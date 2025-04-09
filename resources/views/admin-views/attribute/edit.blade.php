@extends('layouts.admin.app')

@section('title', translate('Update attribute'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/attribute.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Update_Attribute')}}
                </span>
            </h2>
        </div>

        <div class="card card-body">
            <form action="{{route('admin.attribute.update',[$attribute['id']])}}" method="post">
                @csrf
                @php($data = Helpers::get_business_settings('language'))
                @php($defaultLang = Helpers::get_default_language())

                @if($data && array_key_exists('code', $data[0]))
                    <ul class="nav nav-tabs w-fit-content mb-4">
                        @foreach($data as $lang)
                            <li class="nav-item">
                                <a class="nav-link lang_link {{$lang['default'] == true ? 'active':''}}" href="#" id="{{$lang['code']}}-link">
                                    {{ Helpers::get_language_name($lang['code']).'('.strtoupper($lang['code']).')' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="row">
                        <div class="col-12">
                            @foreach($data as $lang)

                                <?php
                                if(count($attribute['translations'])){
                                    $translate = [];
                                    foreach($attribute['translations'] as $t)
                                    {
                                        if($t->locale == $lang['code'] && $t->key=="name"){
                                            $translate[$lang['code']]['name'] = $t->value;
                                        }
                                    }
                                }
                                ?>
                                <div class="form-group lang_form {{$lang['default'] == false ? 'd-none':''}}" id="{{$lang['code']}}-form">
                                    <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} ({{strtoupper($lang['code'])}})</label>
                                    <input type="text" name="name[]" class="form-control"
                                        placeholder="{{translate('New attribute')}}"
                                        value="{{$lang['code'] == 'en' ? $attribute['name'] : ($translate[$lang['code']]['name']??'')}}"
                                        {{$lang['status'] == true ? 'required':''}} maxlength="255"
                                        @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                </div>
                                <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group lang_form" id="{{$defaultLang}}-form">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} ({{strtoupper($defaultLang)}})</label>
                                <input type="text" name="name[]" class="form-control" value="{{ $attribute['name'] }}" placeholder="{{translate('New attribute')}}" maxlength="255">
                            </div>
                            <input type="hidden" name="lang[]" value="{{$defaultLang}}">
                        </div>
                    </div>
                @endif
                <div class="d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $(".lang_link").click(function(e){
            e.preventDefault();

            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.split("-")[0];

            $("#"+lang+"-form").removeClass('d-none');
            if(lang == '{{$defaultLang}}')
            {
                $(".from_part_2").removeClass('d-none');
            }
            else
            {
                $(".from_part_2").addClass('d-none');
            }
        });
    </script>
@endpush

