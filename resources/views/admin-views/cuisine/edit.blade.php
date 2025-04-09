@extends('layouts.admin.app')

@section('title', translate('Update Cuisine'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/category.png')}}" alt="">
                <span class="page-header-title">{{translate('Cuisine Update')}}</h1></span>
            </h2>
        </div>

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.cuisine.update', [$cuisine['id']])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        @php($data = Helpers::get_business_settings('language'))
                        @php($defaultLang = Helpers::get_default_language())

                        @if ($data && array_key_exists('code', $data[0]))
                            <ul class="nav w-fit-content nav-tabs mb-4 ml-3">
                                @foreach ($data as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link {{ $lang['code'] == 'en' ? 'active' : '' }}" href="#"
                                           id="{{ $lang['code'] }}-link">{{ Helpers::get_language_name($lang['code']) . '(' . strtoupper($lang['code']) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    @foreach($data as $lang)
                                            <?php
                                            if(count($cuisine['translations'])){
                                                $translate = [];
                                                foreach($cuisine['translations'] as $t)
                                                {
                                                    if($t->locale == $lang['code'] && $t->key=="name"){
                                                        $translate[$lang['code']]['name'] = $t->value;
                                                    }
                                                    if($t->locale == $lang['code'] && $t->key=="sub_title"){
                                                        $translate[$lang['code']]['sub_title'] = $t->value;
                                                    }
                                                }
                                            }
                                            ?>
                                        <div class="{{$lang['code'] != 'en'? 'd-none':''}} lang_form" id="{{$lang['code']}}-form">
                                            <div class="form-group">
                                                <label class="input-label" for="{{$lang['code']}}_name">{{translate('name')}} ({{strtoupper($lang['code'])}})</label>
                                                <input type="text" name="name[]" id="{{$lang['code']}}_name" class="form-control"
{{--                                                       value="{{$translate[$lang['code']]['name']??$cuisine['name']}}"--}}
                                                       value="{{$lang['code'] == 'en' ? $cuisine['name'] : ($translate[$lang['code']]['name']??'')}}"
                                                       @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif
                                                       placeholder="{{translate('Thai')}}" {{$lang['status'] == true ? 'required':''}}>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                            <div class="form-group">
                                                <label class="input-label" for="{{$lang['code']}}_sub_title">{{translate('sub_title')}}  ({{strtoupper($lang['code'])}})</label>
                                                <input type="text" name="sub_title[]" class="form-control" placeholder="Ex:{{translate('The national dish of Thailand')}}"
{{--                                                       value="{{$translate[$lang['code']]['sub_title']??$cuisine['sub_title']}}"--}}
                                                       value="{{$lang['code'] == 'en' ? $cuisine['sub_title'] : ($translate[$lang['code']]['sub_title']??'')}}"
                                                       @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif
                                                       maxlength="255" id="{{$lang['code']}}_hiddenArea" {{$lang['status'] == true ? 'required':''}}>
                                            </div>
                                        </div>
                                    @endforeach

                                        @else
                                            <div id="english-form">
                                                <div class="form-group">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} (EN)</label>
                                                    <input type="text" name="name[]" value="{{$cuisine['name']}}" class="form-control" placeholder="{{translate('Thai')}}" required>
                                                </div>
                                                <input type="hidden" name="lang[]" value="en">
                                                <div class="form-group">
                                                    <label class="input-label" for="exampleFormControlInput1">{{translate('sub_title')}} (EN)</label>
                                                    <input type="text" name="sub_title[]" class="form-control" id="hiddenArea" value="{{$cuisine['sub_title']}}" maxlength="255" placeholder="Ex:{{translate('The national dish of Thailand')}}">
                                                </div>
                                            </div>
                                        @endif

                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <label class="mb-0">{{translate('Image')}}</label>
                                            <small class="text-danger">( {{translate('ratio 1:1')}} )</small>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <div class="upload-file">
                                                <input type="file" name="image" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" class="upload-file__input">
                                                <div class="upload-file__img_drag upload-file__img max-h-200px overflow-hidden">
                                                    <img width="465" src="{{$cuisine['imageFullPath']}}" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <button type="reset" id="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/read-url.js')}}"></script>
    <script>
        $(".lang_link").click(function(e){
            e.preventDefault();
            $(".lang_link").removeClass('active');
            $(".lang_form").addClass('d-none');
            $(this).addClass('active');

            let form_id = this.id;
            let lang = form_id.split("-")[0];
            console.log(lang);
            $("#"+lang+"-form").removeClass('d-none');
            if(lang == 'en')
            {
                $("#from_part_2").removeClass('d-none');
            }
            else
            {
                $("#from_part_2").addClass('d-none');
            }
        })
    </script>
@endpush
