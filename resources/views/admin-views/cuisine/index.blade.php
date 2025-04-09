@extends('layouts.admin.app')

@section('title', translate('Add new cuisine'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/category.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Add_New_Cuisine')}}
                </span>
            </h2>
        </div>

        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <form action="{{route('admin.cuisine.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        @php($data = Helpers::get_business_settings('language'))
                        @php($defaultLang = Helpers::get_default_language())

                        @if ($data && array_key_exists('code', $data[0]))
                            <ul class="nav w-fit-content nav-tabs mb-4 ml-3">
                                @foreach ($data as $lang)
                                    <li class="nav-item">
                                        <a class="nav-link lang_link {{ $lang['default'] == true ? 'active' : '' }}" href="#"
                                           id="{{ $lang['code'] }}-link">{{ Helpers::get_language_name($lang['code']) . '(' . strtoupper($lang['code']) . ')' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    @foreach($data as $lang)
                                        <div class="{{$lang['default'] == false ? 'd-none':''}} lang_form" id="{{$lang['code']}}-form">
                                            <div class="form-group">
                                                <label class="input-label" for="{{$lang['code']}}_name">{{translate('name')}} ({{strtoupper($lang['code'])}})</label>
                                                <input type="text" name="name[]" id="{{$lang['code']}}_name" class="form-control"
                                                       placeholder="{{translate('Thai')}}" {{$lang['status'] == true ? 'required':''}}
                                                       @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                            <div class="form-group">
                                                <label class="input-label"
                                                       for="{{$lang['code']}}_sub_title">{{translate('sub_title')}}  ({{strtoupper($lang['code'])}})</label>
                                                <input type="text" name="sub_title[]" class="form-control" placeholder="Ex:{{translate('The national dish of Thailand')}}"
                                                       maxlength="255" id="{{$lang['code']}}_hiddenArea"
                                                       @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif
                                                    {{$lang['status'] == true ? 'required':''}}>
                                            </div>
                                        </div>
                                    @endforeach
                                    @else
                                        <div class="" id="{{$defaultLang}}-form">
                                            <div class="form-group">
                                                <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} (EN)</label>
                                                <input type="text" name="name[]" class="form-control" placeholder="{{translate('Thai')}}" required>
                                            </div>
                                            <input type="hidden" name="lang[]" value="en">
                                            <div class="form-group">
                                                <label class="input-label" for="exampleFormControlInput1">{{translate('sub_title')}} (EN)</label>
                                                <input type="text" name="sub_title[]" class="form-control" id="hiddenArea"  maxlength="255" placeholder="Ex:{{translate('The national dish of Thailand')}}"></input>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <label class="mb-0">{{translate('Image')}}</label>
                                            <small class="text-danger">* ( {{translate('ratio 1:1')}} )</small>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <div class="upload-file cuisine-image">
                                                <input type="file" name="image" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" class="upload-file__input" required>
                                                <div class="upload-file__img_drag upload-file__img width-300px max-h-200px overflow-hidden">
                                                    <img width="465" id="viewer" src="{{asset('public/assets/admin/img/icons/upload_img2.png')}}" alt="">
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

        <div class="row g-3">
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex gap-1 mb-0">
                                    {{translate('cuisine_Table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $cuisines->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search"
                                            class="form-control"
                                            placeholder="{{translate('Search by cuisine name')}}" aria-label="Search"
                                            value="{{$search}}" required autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{translate('SL')}}</th>
                                        <th>{{translate('cuisine_Image')}}</th>
                                        <th>{{translate('name')}}</th>
                                        <th>{{translate('sub_title')}}</th>
                                        <th>{{translate('status')}}</th>
                                        <th>{{translate('priority')}}</th>
                                        <th class="text-center">{{translate('action')}}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                @foreach($cuisines as $key=>$cuisine)
                                    <tr>
                                        <td>{{$cuisines->firstitem()+$key}}</td>
                                        <td>
                                            <div>
                                                <img width="50" class="avatar-img rounded" src="{{$cuisine->imageFullPath}}" alt="">
                                            </div>
                                        </td>
                                        <td><div class="text-capitalize text-wrap">{{$cuisine['name']}}</div></td>
                                        <td><div class="text-capitalize text-wrap">{{$cuisine['sub_title']}}</div></td>
                                        <td>
                                            <div>
                                                <label class="switcher">
                                                    <input class="switcher_input status-change" type="checkbox" {{$cuisine['is_active']==1? 'checked' : ''}} id="cuisine-{{$cuisine['id']}}"
                                                           data-url="{{route('admin.cuisine.status',[$cuisine['id'],1])}}">
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="max-w100 min-w80px">
                                                <select name="priority" class="custom-select redirect-url-value"
                                                        data-url="{{ route('admin.cuisine.priority', ['id' => $cuisine['id'], 'priority' => '']) }}">
                                                    @for($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}" {{ $cuisine->priority == $i ? 'selected' : '' }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                href="{{route('admin.cuisine.edit',[$cuisine['id']])}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                    data-id="cuisine--{{$cuisine['id']}}" data-message="{{translate('Want to delete this cuisine?')}}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.cuisine.delete',[$cuisine['id']])}}"
                                                method="post" id="cuisine--{{$cuisine['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $cuisines->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
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
            if(lang == '{{$defaultLang}}')
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
