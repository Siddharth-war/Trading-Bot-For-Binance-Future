@extends('layouts.admin.app')

@section('title', translate('Add new notification'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-notifications"></i>
                <span class="page-header-title">
                    {{translate('send_Notification')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <form action="{{route('admin.notification.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="input-label">{{translate('title')}}
                                            <i class="tio-info text-danger" data-toggle="tooltip" data-placement="right"
                                               title="{{ translate('not_more_than_100_characters') }}">
                                            </i>
                                        </label>
                                        <input type="text" name="title" maxlength="100" class="form-control" value="{{ old('title') }}" placeholder="{{translate('New notification')}}" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="input-label">{{translate('description')}}
                                            <i class="tio-info text-danger" data-toggle="tooltip" data-placement="right"
                                               title="{{ translate('not_more_than_255_characters') }}">
                                            </i>
                                        </label>
                                        <textarea name="description" maxlength="255" class="form-control" rows="3" placeholder="{{translate('Description...')}}" required>{{ old('description') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <label class="mb-0">{{translate('notification_Banner')}}</label>
                                            <small class="text-danger">* ( {{translate('ratio')}} 3:1 )</small>
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
                                            <div class="upload-file">
                                                <input type="file" name="image" accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" class="upload-file__input">
                                                <div class="upload-file__img_drag upload-file__img max-h-200px overflow-hidden">
                                                    <img width="465" id="viewer" src="{{asset('public/assets/admin/img/icons/upload_img2.png')}}" alt="">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" id="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('send_notification')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-12">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row justify-content-between align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex align-items-center gap-2 mb-0">
                                    {{translate('Notification_Table')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $notifications->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by title or description')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
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


                    <div class="py-3">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('image')}}</th>
                                    <th>{{translate('title')}}</th>
                                    <th>{{translate('description')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($notifications as $key=>$notification)
                                    <tr>
                                        <td>{{$notifications->firstitem()+$key}}</td>
                                        <td>
                                            @if($notification['image']!=null)
                                                <img class="img-vertical-150" src="{{$notification['imageFullPath']}}" alt="{{ translate('notification') }}">
                                            @else
                                                <label class="badge badge-soft-warning">{{translate('No')}} {{translate('image')}}</label>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="max-w300 text-wrap">
                                                {{substr($notification['title'],0,25)}} {{strlen($notification['title'])>25?'...':''}}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="max-w300 text-wrap">
                                                {{substr($notification['description'],0,25)}} {{strlen($notification['description'])>25?'...':''}}
                                            </div>
                                        </td>
                                        <td>
                                            <label class="switcher">
                                                <input class="switcher_input status-change" type="checkbox" id="{{$notification['id']}}"
                                                    data-url="{{route('admin.notification.status',[$notification['id'],0])}}" {{$notification['status'] == 1? 'checked' : ''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                href="{{route('admin.notification.edit',[$notification['id']])}}"><i class="tio-edit"></i></a>
                                                <button type="button" class="btn btn-outline-danger btn-sm delete square-btn notification-delete-btn" data-id="{{$notification['id']}}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form
                                                action="{{route('admin.notification.delete',[$notification['id']])}}"
                                                method="post" id="notification-{{$notification['id']}}">
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
                                {!! $notifications->links() !!}
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
        "use strict";

        $('.notification-delete-btn').click(function() {
            var notificationId = $(this).data('id');
            $('#notification-' + notificationId).submit();
        });
    </script>
@endpush
