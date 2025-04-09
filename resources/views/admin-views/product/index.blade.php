@extends('layouts.admin.app')
@section('title', translate('Add new product'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('public/assets/admin/css/tags-input.min.css')}}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Add_New_Product')}}
                </span>
            </h2>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <form action="javascript:" method="post" id="product_form" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2">
                        <div class="col-lg-6">
                            <div class="card card-body h-100">
                                @php($data = Helpers::get_business_settings('language'))
                                @php($defaultLang = Helpers::get_default_language())

                                @if($data && array_key_exists('code', $data[0]))
                                    <ul class="nav nav-tabs mb-4">

                                        @foreach($data as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link {{$lang['default'] == true ? 'active':''}}" href="#" id="{{$lang['code']}}-link">{{Helpers::get_language_name($lang['code']).'('.strtoupper($lang['code']).')'}}</a>
                                            </li>
                                        @endforeach

                                    </ul>
                                    @foreach($data as $lang)
                                        <div class="{{$lang['default'] == false ? 'd-none':''}} lang_form" id="{{$lang['code']}}-form">
                                            <div class="form-group">
                                                <label class="input-label" for="{{$lang['code']}}_name">{{translate('name')}} ({{strtoupper($lang['code'])}})</label>
                                                <input type="text" name="name[]" id="{{$lang['code']}}_name" class="form-control"
                                                       placeholder="{{translate('New Product')}}" {{$lang['status'] == true ? 'required':''}}
                                                       @if($lang['status'] == true) oninvalid="document.getElementById('{{$lang['code']}}-link').click()" @endif>
                                            </div>
                                            <input type="hidden" name="lang[]" value="{{$lang['code']}}">
                                            <div class="form-group">
                                                <label class="input-label"
                                                       for="{{$lang['code']}}_description">{{translate('short')}} {{translate('description')}}  ({{strtoupper($lang['code'])}})</label>
                                                <textarea name="description[]" class="form-control textarea-h-100" id="{{$lang['code']}}_hiddenArea"></textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="" id="{{$defaultLang}}-form">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('name')}} (EN)</label>
                                            <input type="text" name="name[]" class="form-control" placeholder="{{translate('New Product')}}" required>
                                        </div>
                                        <input type="hidden" name="lang[]" value="en">
                                        <div class="form-group">
                                            <label class="input-label"
                                                   for="exampleFormControlInput1">{{translate('short')}} {{translate('description')}} (EN)</label>
                                            <textarea name="description[]" class="form-control textarea-h-100" id="hiddenArea"></textarea>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card card-body h-100">
                                <div class="form-group">
                                    <label class="font-weight-bold text-dark">{{translate('product_Image')}}</label>
                                    <small class="text-danger">* ( {{translate('ratio')}} 1:1 )</small>
                                    <div class="d-flex justify-content-center mt-4">
                                        <div class="upload-file">
                                            <input type="file" name="image" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" class="upload-file__input">
                                            <div class="upload-file__img_drag upload-file__img">
                                                <img width="176" src="{{asset('public/assets/admin/img/icons/upload_img.png')}}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-category"></i>
                                                {{translate('Category')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="exampleFormControlSelect1">
                                                            {{translate('category')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="category_id" class="form-control js-select2-custom"
                                                                onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-categories')">
                                                            <option selected disabled>---{{translate('select')}}---</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{$category['id']}}">{{$category['name']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="exampleFormControlSelect1">{{translate('sub_category')}}<span
                                                                class="input-label-secondary"></span></label>
                                                        <select name="sub_category_id" id="sub-categories"
                                                                class="form-control js-select2-custom"
                                                                onchange="getRequest('{{url('/')}}/admin/product/get-categories?parent_id='+this.value,'sub-sub-categories')">
                                                            <option selected disabled>---{{translate('select')}}---</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label" for="exampleFormControlInput1">
                                                            {{translate('item_Type')}}
                                                            <span class="text-danger">*</span>
                                                        </label>

                                                        <select name="item_type" class="form-control js-select2-custom">
                                                            <option selected disabled>---{{translate('select')}}---</option>
                                                            <option value="0">{{translate('product')}} {{translate('item')}}</option>
                                                            <option value="1">{{translate('set_menu')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label">
                                                            {{translate('product_Type')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="product_type" class="form-control js-select2-custom">
                                                            <option selected disabled>---{{translate('select')}}---</option>
                                                            <option value="veg">{{translate('veg')}}</option>
                                                            <option value="non_veg">{{translate('nonveg')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-dollar"></i>
                                                {{translate('Price_Information')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('default_Price')}}
                                                            <span class="text-danger">*</span></label>
                                                        <input type="number" min="0" step="any" value="1" name="price" class="form-control"
                                                               placeholder="{{translate('Ex : 100')}}" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('discount_Type')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="discount_type" class="form-control js-select2-custom" id="discount_type">
                                                            <option selected disabled>---{{translate('select')}}---</option>
                                                            <option value="percent">{{translate('percentage')}}</option>
                                                            <option value="amount">{{translate('amount')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label id="discount_label" class="input-label">{{translate('discount')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input id="discount_input" type="number" min="0" name="discount" class="form-control"
                                                               placeholder="{{translate('Ex : 5%')}}" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('tax_Type')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="tax_type" class="form-control js-select2-custom" id="tax_type">
                                                            <option selected disabled>---{{translate('select')}}---</option>
                                                            <option value="percent">{{translate('percentage')}}</option>
                                                            <option value="amount">{{translate('amount')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label id="tax_label" class="input-label" for="exampleFormControlInput1">{{translate('tax_Rate')}}
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input id="tax_input" type="number" min="0" step="any" name="tax" class="form-control"
                                                               placeholder="{{translate('Ex : $100')}}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-dollar"></i>
                                                {{translate('Stock Information')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('Stock Type')}}</label>
                                                        <select name="stock_type" class="form-control js-select2-custom" id="stock_type">
                                                            <option value="unlimited">{{translate('unlimited')}}</option>
                                                            <option value="daily">{{translate('daily')}}</option>
                                                            <option value="fixed">{{translate('fixed')}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 d-none" id="product_stock_div">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('Product Stock')}}
                                                        </label>
                                                        <input id="product_stock" type="number" name="product_stock" class="form-control"
                                                               placeholder="{{translate('Ex : 10')}}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row g-2">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                <div class="text-dark">{{ translate('turning visibility off will not show this product in the user app and website') }}</div>
                                                <div class="d-flex gap-3 align-items-center">
                                                    <h5>{{translate('Visibility')}}</h5>
                                                    <label class="switcher">
                                                        <input class="switcher_input" type="checkbox" checked="checked" name="status">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-watches"></i>
                                                {{translate('Availability')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('available_From')}}</label>
                                                        <input type="time" name="available_time_starts" class="form-control" value="10:30:00"
                                                               placeholder="{{translate('Ex : 10:30 am')}}" required>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="form-group">
                                                        <label class="input-label">{{translate('available_Till')}}</label>
                                                        <input type="time" name="available_time_ends" class="form-control" value="19:30:00" placeholder="{{translate('5:45 pm')}}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-puzzle"></i>
                                                {{translate('Addons')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label class="input-label">{{translate('Select_Addons')}}</label>
                                                <select name="addon_ids[]" class="form-control" id="choose_addons" multiple="multiple">
                                                    @foreach(\App\Model\AddOn::orderBy('name')->get() as $addon)
                                                        <option value="{{$addon['id']}}">{{$addon['name']}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-label"></i>
                                                {{translate('tags')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="">
                                                        <label class="input-label">{{translate('search_tag')}}</label>
                                                        <input type="text" class="form-control" name="tags" placeholder="Enter tags" data-role="tagsinput">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between gap-3">
                                                <div class="text-dark">{{ translate('turning recommended status on will show this product in the chef recommended section in user app and website') }}</div>
                                                <div class="d-flex gap-3 align-items-center">
                                                    <h5>{{translate('Recommended')}}</h5>
                                                    <label class="switcher">
                                                        <input class="switcher_input" type="checkbox" checked="checked" name="is_recommended">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                                <i class="tio-label"></i>
                                                {{translate('cuisine')}}
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="">
                                                        <label class="input-label">{{translate('select cuisine')}}</label>
                                                        <select name="cuisines[]" class="form-control js-select2-custom" multiple>
                                                            <option value="" disabled>---{{translate('select cuisine')}}---</option>
                                                            @foreach($cuisines as $cuisine)
                                                                <option value="{{$cuisine['id']}}">{{$cuisine['name']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="mb-0 d-flex gap-2 align-items-center">
                                <i class="tio-canvas-text"></i>
                                {{ translate('product_Variations') }}
                            </h4>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-12">
                                    <div id="add_new_option">
                                    </div>
                                    <br>
                                    <div class="">
                                        <a class="btn btn-outline-success"
                                           id="add_new_option_button">{{ translate('add_New_Variation') }}</a>
                                    </div>
                                    <br><br>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>


    <script>
        var count = 0;
        $(document).ready(function() {

            $("#add_new_option_button").click(function(e) {
                count++;
                var add_option_view = `
                    <div class="card view_new_option mb-2" >
                        <div class="card-header">
                            <label for="" id=new_option_name_` + count + `> {{ translate('add_new') }}</label>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-lg-3 col-md-6">
                                    <label for="">{{ translate('name') }}</label>
                                    <input required name=options[` + count + `][name] class="form-control" type="text"
                                        onkeyup="new_option_name(this.value,` + count + `)">
                                </div>

                                <div class="col-lg-3 col-md-6">
                                    <div class="form-group">
                                        <label class="input-label text-capitalize d-flex alig-items-center"><span class="line--limit-1">{{ translate('selcetion_type') }} </span></label>
                                        <div class="resturant-type-group border">
                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="multi "name="options[` + count + `][type]" id="type` + count +
                                                    `" checked onchange="show_min_max(` + count + `)">
                                                <span class="form-check-label">{{ translate('Multiple') }}</span>
                                            </label>

                                            <label class="form-check form--check mr-2 mr-md-4">
                                                <input class="form-check-input" type="radio" value="single" name="options[` + count + `][type]" id="type` + count +
                                                    `" onchange="hide_min_max(` + count + `)" >
                                                <span class="form-check-label">{{ translate('Single') }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="row g-2">
                                        <div class="col-sm-6 col-md-4">
                                            <label for="">{{ translate('Min') }}</label>
                                            <input id="min_max1_` + count + `" required  name="options[` + count + `][min]" class="form-control" type="number" min="1">
                                        </div>
                                        <div class="col-sm-6 col-md-4">
                                            <label for="">{{ translate('Max') }}</label>
                                            <input id="min_max2_` + count + `"   required name="options[` + count + `][max]" class="form-control" type="number" min="1">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="d-md-block d-none">&nbsp;</label>
                                        <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <input id="options[` + count + `][required]" name="options[` + count + `][required]" type="checkbox">
                                            <label for="options[` + count + `][required]" class="m-0">{{ translate('Required') }}</label>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-danger btn-sm delete_input_button" onclick="removeOption(this)"title="{{ translate('Delete') }}">
                                                <i class="tio-add-to-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="option_price_` + count + `" >
                        <div class="border rounded p-3 pb-0 mt-3">
                            <div  id="option_price_view_` + count + `">
                                <div class="row g-3 add_new_view_row_class mb-3">
                                    <div class="col-md-4 col-sm-6">
                                        <label for="">{{ translate('Option_name') }}</label>
                                        <input class="form-control" required type="text" name="options[` + count +`][values][0][label]" id="">
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label for="">{{ translate('Additional_price') }}</label>
                                        <input class="form-control" required type="number" min="0" step="0.01" name="options[` + count + `][values][0][optionPrice]" id="">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3 p-3 mr-1 d-flex "  id="add_new_button_` + count + `">
                                <button type="button" class="btn btn-outline-primary" onclick="add_new_row_button(` +
                                    count + `)" >{{ translate('Add_New_Option') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;

            $("#add_new_option").append(add_option_view);
            });
        });

        function show_min_max(data) {
            $('#min_max1_' + data).removeAttr("readonly");
            $('#min_max2_' + data).removeAttr("readonly");
            $('#min_max1_' + data).attr("required", "true");
            $('#min_max2_' + data).attr("required", "true");
        }

        function hide_min_max(data) {
            $('#min_max1_' + data).val(null).trigger('change');
            $('#min_max2_' + data).val(null).trigger('change');
            $('#min_max1_' + data).attr("readonly", "true");
            $('#min_max2_' + data).attr("readonly", "true");
            $('#min_max1_' + data).attr("required", "false");
            $('#min_max2_' + data).attr("required", "false");
        }

        function new_option_name(value, data) {
            $("#new_option_name_" + data).empty();
            $("#new_option_name_" + data).text(value)
            console.log(value);
        }

        function removeOption(e) {
            element = $(e);
            element.parents('.view_new_option').remove();
        }

        function deleteRow(e) {
            element = $(e);
            element.parents('.add_new_view_row_class').remove();
        }


        function add_new_row_button(data) {
            count = data;
            countRow = 1 + $('#option_price_view_' + data).children('.add_new_view_row_class').length;
            var add_new_row_view = `
                <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-sm-0">
                    <div class="col-md-4 col-sm-5">
                        <label for="">{{ translate('Option_name') }}</label>
                        <input class="form-control" required type="text" name="options[` + count + `][values][` + countRow + `][label]" id="">
                    </div>
                    <div class="col-md-4 col-sm-5">
                        <label for="">{{ translate('Additional_price') }}</label>
                        <input class="form-control"  required type="number" min="0" step="0.01" name="options[` + count + `][values][` + countRow + `][optionPrice]" id="">
                    </div>
                    <div class="col-sm-2 max-sm-absolute">
                        <label class="d-none d-sm-block">&nbsp;</label>
                        <div class="mt-1">
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)"title="{{ translate('Delete') }}">
                                    <i class="tio-add-to-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>`;
            $('#option_price_view_' + data).append(add_new_row_view);
        }
    </script>


    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
            $('#image-viewer-section').show(1000)
        });
    </script>

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

    <script>
        //Select 2
        $("#choose_addons").select2({
            placeholder: "Select Addons",
            allowClear: true
        });

    </script>

    <script>


        $('#product_form').on('submit', function () {
            var formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.product.store')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{translate("product added successfully!")}}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.product.list')}}';
                        }, 2000);
                    }
                }
            });
        });
    </script>

    <script>
        function getRequest(route, id) {
            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });
        }
    </script>

    <script>
        $(document).on('ready', function () {
            $('.js-select2-custom').each(function () {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
    </script>

    <script src="{{asset('public/assets/admin')}}/js/tags-input.min.js"></script>


    <script>
        function update_qty() {
            var total_qty = 0;
            var qty_elements = $('input[name^="stock_"]');
            for(var i=0; i<qty_elements.length; i++)
            {
                total_qty += parseInt(qty_elements.eq(i).val());
            }
            if(qty_elements.length > 0)
            {
                $('input[name="total_stock"]').attr("readonly", true);
                $('input[name="total_stock"]').val(total_qty);
                console.log(total_qty)
            }
            else{
                $('input[name="total_stock"]').attr("readonly", false);
            }
        }
    </script>

    <script>
        $("#discount_type").change(function(){
            if(this.value === 'amount') {
                $("#discount_label").text("{{translate('discount_amount')}}");
                $("#discount_input").attr("placeholder", "{{translate('Ex: 500')}}")
            }
            else if(this.value === 'percent') {
                $("#discount_label").text("{{translate('discount_percent')}}")
                $("#discount_input").attr("placeholder", "{{translate('Ex: 50%')}}")
            }
        });

        $("#tax_type").change(function(){
            if(this.value === 'amount') {
                $("#tax_label").text("{{translate('tax_amount')}}");
                $("#tax_input").attr("placeholder", "{{translate('Ex: 500')}}")
            }
            else if(this.value === 'percent') {
                $("#tax_label").text("{{translate('tax_percent')}}")
                $("#tax_input").attr("placeholder", "{{translate('Ex: 50%')}}")
            }
        });


        $("#stock_type").change(function(){
            if(this.value === 'daily' || this.value === 'fixed') {
               $("#product_stock_div").removeClass('d-none')
            }
            else {
                $("#product_stock_div").addClass('d-none')
            }
        });



    </script>
@endpush




