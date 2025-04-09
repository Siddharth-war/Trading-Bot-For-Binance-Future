@extends('layouts.admin.app')

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
                <div class="card-body">
                    <form action="{{route('admin.kitchen.update',[$chef['id']])}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="input-label">{{translate('Select Branch')}} <span class="text-danger">*</span></label>
                                    <select name="branch_id" class="custom-select" required>
                                        <option disabled selected>{{ translate('--select_Branch--') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{$branch['id']}}" {{ $branch->id == ($chef?->chefBranch->branch_id?? 0) ? 'selected' : '' }}>{{$branch['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="f_name">
                                    {{translate('First_Name')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="f_name" value="{{$chef['f_name']}}" class="form-control" id="f_name" placeholder="{{translate('Ex')}} : {{translate('John')}}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="l_name">
                                    {{translate('Last_Name')}}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="l_name" value="{{$chef['l_name']}}" class="form-control" id="l_name" placeholder="{{translate('Ex')}} : {{translate('Doe')}}">
                            </div>
                        </div>

                        <div class="row">
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
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name">{{translate('Password')}}</label><small> ( {{translate('input if you want to change')}} )</small>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="password" class="js-toggle-password form-control form-control input-field" id="password"
                                           placeholder="{{translate('Password')}}"
                                           data-hs-toggle-password-options='{
                                        "target": "#changePassTarget",
                                        "defaultClass": "tio-hidden-outlined",
                                        "showClass": "tio-visible-outlined",
                                        "classChangeTarget": "#changePassIcon"
                                        }'>
                                    <div id="changePassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changePassIcon" class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password">{{translate('confirm_Password')}}</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="confirm_password" class="js-toggle-password form-control form-control input-field" id="confirm_password"
                                           placeholder="{{translate('confirm password')}}"
                                           data-hs-toggle-password-options='{
                                        "target": "#changeConPassTarget",
                                        "defaultClass": "tio-hidden-outlined",
                                        "showClass": "tio-visible-outlined",
                                        "classChangeTarget": "#changeConPassIcon"
                                        }'>
                                    <div id="changeConPassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changeConPassIcon" class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="name">{{translate('image')}} <span class="text-danger">*</span> </label>
                                    <span class="badge badge-soft-danger">( {{translate('ratio')}} 1:1 )</span>
                                    <div class="custom-file text-left">
                                        <input type="file" name="image" id="customFileUpload" class="custom-file-input"
                                               accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileUpload">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <img class="upload-img-view" id="viewer"
                                         src="{{$chef->imageFullPath}}" alt="{{ translate('chef') }}">
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
@endpush
@push('script_2')
    <script src="{{asset('public/assets/admin/js/image-upload.js')}}"></script>
@endpush
