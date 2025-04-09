@extends('layouts.branch.app')

@section('title', translate('Promotional campaign'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/product.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('campaign_Update')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('branch.promotion.update',[$promotion['id']])}}" method="post" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('Select_Banner_Type')}} <span class="text-danger">*</span></label>
                                        <select name="banner_type" id="banner_type" class="form-control" required>
                                            <option value="" selected>{{ translate('--select--') }}</option>
                                            <option value="bottom_banner" {{ $promotion->promotion_type == 'bottom_banner' ? 'selected' : '' }}>{{ translate('Bottom Banner (1110*380 px)') }}</option>
                                            <option value="top_right_banner" {{ $promotion->promotion_type == 'top_right_banner' ? 'selected' : '' }}>{{ translate('Top Right Banner (280*450 px)') }}</option>
                                            <option value="bottom_right_banner" {{ $promotion->promotion_type == 'bottom_right_banner' ? 'selected' : '' }}>{{ translate('Bottom Right Banner (280*350 px)') }}</option>
                                            <option value="video" {{ $promotion->promotion_type == 'video' ? 'selected' : '' }}>{{ translate('Video') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        @if($promotion->promotion_type == 'video')
                                        <div class="col-12 from_part_2 video_section" id="video_section">
                                            <label class="input-label">{{translate('youtube Video URL')}} <span class="text-danger">*</span></label>
                                            <input type="text" name="video" value="{{$promotion->promotion_name}}" class="form-control" placeholder="{{ translate('ex : https://youtu.be/0sus46BflpU') }}">
                                        </div>
                                        @else
                                        <div class="col-12 from_part_2 image_section" id="image_section">
                                            <label class="input-label">{{translate('Image')}} <span class="text-danger">*</span></label>
                                            <div class="custom-file">
                                                <input type="file" name="image" id="customFileEg" class="custom-file-input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*"
                                                    oninvalid="document.getElementById('en-link').click()">
                                                <label class="custom-file-label" for="customFileEg">{{ translate('choose file') }}</label>
                                            </div>
                                            <div class="col-12 from_part_2 mt-2">
                                                <div class="form-group">
                                                    <div class="text-center">
                                                        <img width="105" class="rounded-10 border" id="viewer"
                                                            src="{{asset('storage/app/public/promotion')}}/{{$promotion['promotion_name']}}" alt="image" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
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

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/branch-promotion.js') }}"></script>

@endpush
