@extends('layouts.branch.app')

@section('title', translate('Chef Edit'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/cooking.png')}}" alt="">
            <span class="page-header-title">
                {{translate('Chef_Update')}}
            </span>
        </h2>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{translate('chef_Update_Form')}}</h5>
                </div>
                <div class="card-body">
                    <form action="{{route('branch.kitchen.update',[$chef['id']])}}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="f_name">{{translate('First Name')}} <span class="text-danger">*</span></label>
                                <input type="text" name="f_name" value="{{$chef['f_name']}}" class="form-control" id="f_name"
                                        placeholder="{{translate('Ex')}} : {{translate('John')}}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="l_name">{{translate('Last Name')}} <span class="text-danger">*</span></label>
                                <input type="text" name="l_name" value="{{$chef['l_name']}}" class="form-control" id="l_name"
                                        placeholder="{{translate('Ex')}} : {{translate('Doe')}}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name">{{translate('Phone')}} <span class="text-danger">*</span></label>
                                <input type="text" value="{{$chef['phone']}}" required name="phone" class="form-control" id="phone"
                                        placeholder="{{translate('Ex')}} : +88017********">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="name">{{translate('Email')}} <span class="text-danger">*</span></label>
                                <input type="email" value="{{$chef['email']}}" name="email" class="form-control" id="email"
                                        placeholder="{{translate('Ex')}} : ex@gmail.com" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="name">{{translate('Password')}}</label><small> ( {{translate('input if you want to change')}} )</small>
                                <input type="password" name="password" class="form-control" id="password"
                                        placeholder="{{translate('Password')}}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="name">{{translate('image')}}</label>
                                    <span class="text-danger">*</span>
                                    <span class="badge badge-soft-danger">( {{translate('ratio')}} 1:1 )</span>
                                    <div class="custom-file text-left">
                                        <input type="file" name="image" id="customFileUpload" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileUpload">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <img class="upload-img-view" id="viewer" src="{{$chef['imageFullPath']}}" alt="image"/>
                                </div>
                            </div>
                        </div>


                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="submit" class="btn btn-primary">{{translate('Update')}}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
    <script src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="{{asset('public/assets/back-end')}}/js/select2.min.js"></script>
    <script src="{{ asset('public/assets/admin/js/image-upload.js') }}"></script>
    <script>
        $(".js-example-theme-single").select2({
            theme: "classic"
        });

        $(".js-example-responsive").select2({
            width: 'resolve'
        });
    </script>

@endpush
