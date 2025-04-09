@extends('layouts.admin.app')

@section('title', translate('Product Bulk Import'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/bulk_import.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Bulk_Import')}}
                </span>
            </h2>
        </div>

        <div class="row g-3">
            <div class="col-12">
                <div class="card card-body">
                    <h2>{{translate('Instructions :')}} </h2>

                    <ol class="order-list">
                        <li>{{translate('Download the format file and fill it with proper data.')}}</li>
                        <li>{{translate('You can download the example file to understand how the data must be filled.')}}</li>
                        <li>{{translate('Once you have downloaded and filled the format file, upload it in the form below and submit.')}}</li>
                        <li>{{translate("After uploading products you need to edit them and set product's images and choices.")}}</li>
                        <li>{{translate('You can get category and sub-category id from their list, please input the right ids.')}}</li>
                    </ol>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card card-body">
                    <form class="product-form" action="{{route('admin.product.bulk-import')}}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="rest-part">
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <h4 class="mb-0">{{translate('Do_not_have_the_template')}}?</h4>
                                <a href="{{asset('public/assets/product_bulk_format.xlsx')}}" download=""
                                class="fz-16 btn-link">{{translate('Download_Here')}}</a>
                            </div>
                            <div class="mt-5">
                                <div class="form-group">
                                    <div class="row justify-content-center">
                                        <div class="col-auto">
                                            <div class="upload-file">
                                                <input type="file" id="import-file" name="products_file" accept=".xlsx, .xls" class="upload-file__input">
                                                <div class="upload-file__img_drag upload-file__img">
                                                    <img src="{{asset('public/assets/admin/img/icons/drug_file.png')}}" alt="">
                                                </div>
                                                <div class="file--img"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-3">
                                    <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                    <button type="submit" class="btn btn-primary">{{translate('Submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        $('#import-file').on('change', function(){
            if($(this)[0].files.length !== 0){
                $('.file--img').empty().append(`<div class="my-2"> <img width="200" src="{{asset('public/assets/admin/img/icons/excel.png')}}" alt=""></div>`)
            }
        })
        $('.product-form').on('reset', function(){
            $('.file--img').empty()
        })

    </script>

@endpush
