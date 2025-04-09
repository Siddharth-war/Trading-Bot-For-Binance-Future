@extends('layouts.branch.app')

@section('title', translate('Promotional campaign'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/promotion.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('promotion_Setup')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('branch.promotion.store')}}" method="post" enctype="multipart/form-data" class="mb-0">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('Select_Banner_Type')}} <span class="text-danger">*</span></label>
                                        <select name="banner_type" id="banner_type" class="form-control js-select2-custom" required>
                                            <option value="" selected>{{ translate('--Select--') }}</option>
                                            <option value="bottom_banner">{{ translate('Bottom Banner (1110*380 px)') }}</option>
                                            <option value="top_right_banner">{{ translate('Top Right Banner (280*450 px)') }}</option>
                                            <option value="bottom_right_banner">{{ translate('Bottom Right Banner (280*350 px)') }}</option>
                                            <option value="video">{{ translate('Video') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="col-12 from_part_2 video_section d--none" id="video_section">
                                            <label class="input-label">{{translate('youtube_Video_URL')}} <span class="text-danger">*</span></label>
                                            <input type="text" name="video" class="form-control" placeholder="{{ translate('ex : https://youtu.be/0sus46BflpU') }}">
                                        </div>
                                        <div class="col-12 from_part_2 image_section d--none" id="image_section">
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
                                                        <img class="promotion-image" id="viewer"
                                                            src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}" alt="image" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="card mt-3">
            <div class="card-top px-card pt-4">
                <div class="row justify-content-between align-items-center gy-2">
                    <div class="col-md-4">
                        <h5 class="d-flex align-items-center gap-2 mb-0">
                            {{translate('Promotional_Campaign_Table')}}
                            <span class="badge badge-soft-dark rounded-50 fz-12">{{$promotions->total()}}</span>
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-md-center gap-2 justify-content-md-center">
                            {{translate('Promotion Status')}} :
                            <label class="switcher">
                                <input type="checkbox" class="switcher_input change-status"
                                        class="toggle-switch-input" {{$branch->branch_promotion_status?'checked':''}}>
                                <span class="switcher_control"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search"
                                        class="form-control"
                                        placeholder="{{translate('Search')}}" aria-label="Search"
                                        value="{{$search}}" required autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        {{translate('Search')}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="py-4">
                <div class="table-responsive">
                    <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Branch')}}</th>
                                <th>{{translate('Promotion type')}}</th>
                                <th>{{translate('Promotion Name')}}</th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($promotions as $k=>$promotion)
                            <tr>
                                <th class="align-middle">{{$k+1}}</th>
                                <td>{{$promotion->branch->name}}</td>
                                <td>
                                    @php
                                        $promotion_type = $promotion['promotion_type'];
                                        echo str_replace('_', ' ', $promotion_type);
                                    @endphp
                                </td>
                                <td>
                                    @if($promotion['promotion_type'] == 'video')
                                        {{$promotion['promotion_name']}}
                                    @else
                                        <div width="50">
                                            <img width="100" src="{{$promotion['PromotionNameFullPath']}}">
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{route('branch.promotion.edit',[$promotion['id']])}}"
                                            class="btn btn-outline-info btn-sm edit square-btn"
                                            title="{{translate('Edit')}}">
                                            <i class="tio-edit"></i>
                                        </a>
                                        <a class="btn btn-outline-danger btn-sm edit square-btn form-alert" title="{{translate('Delete')}}" href="javascript:"
                                           data-id="promotion-{{$promotion['id']}}"
                                           data-message="{{translate('Want to delete this promotion ?')}}">
                                            <i class="tio-delete"></i>
                                        </a>
                                    </div>
                                    <form action="{{route('branch.promotion.delete',[$promotion['id']])}}"
                                            method="post" id="promotion-{{$promotion['id']}}">
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
                        {{$promotions->links()}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/branch-promotion.js') }}"></script>

    <script>
        $('.change-status').on('click', function(){
            location.href = '{{route('branch.promotion.status',[$branch['id'],$branch->branch_promotion_status?0:1])}}';
        })
    </script>
@endpush
