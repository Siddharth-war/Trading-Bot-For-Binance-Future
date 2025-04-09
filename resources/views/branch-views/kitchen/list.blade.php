@extends('layouts.branch.app')

@section('title', translate('Chef List'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/cooking.png')}}" alt="">
            <span class="page-header-title">
                {{translate('Chef_List')}}
            </span>
        </h2>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
            <div class="card-top px-card pt-4">
                    <div class="row justify-content-between align-items-center gy-2">
                        <div class="col-md-4">
                            <h5 class="d-flex gap-2 mb-0">
                                {{translate('chef_Table')}}
                                <span class="badge badge-soft-dark rounded-50 fz-12">{{$chefs->total()}}</span>
                            </h5>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap justify-content-md-end gap-3">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search"
                                            class="form-control"
                                            placeholder="{{translate('Search_By_Name')}}" aria-label="Search"
                                            value="{{$search}}" required autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                {{translate('Search')}}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <a href="{{route('branch.kitchen.add-new')}}" class="btn btn-primary text-nowrap">
                                    <i class="tio-add"></i>
                                    <span class="text"> {{translate('Add_New')}}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-4">
                    <div class="table-responsive">
                        <table id="datatable" class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{translate('SL')}}</th>
                                <th>{{translate('Name')}}</th>
                                <th>{{translate('Contact Info')}}</th>
                                <th>{{translate('Branch')}}</th>
                                <th>{{translate('Status')}}</th>
                                <th class="text-center">{{translate('action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($chefs as $k=>$chef)
                                <tr>
                                    <td scope="row">{{$chefs->firstItem()+$k}}</td>
                                    <td class="text-capitalize">{{$chef['f_name'] . ' ' . $chef['l_name']}}</td>
                                    <td>
                                        <div><a class="text-dark" href="mailto:{{$chef['email']}}"><strong>{{$chef['email']}}</strong></a></div>
                                        <div><a href="tel:{{$chef['phone']}}" class="text-dark">{{$chef['phone']}}</a></div>
                                    </td>
                                    <td>{{ \App\User::get_chef_branch_name($chef) }}</td>
                                    <td>
                                        <label class="switcher">
                                            <input type="checkbox"
                                                   data-chef="{{$chef['id']}}"
                                                   data-status="{{$chef->is_active?0:1}}"
                                                   class="switcher_input change-status toggle-switch-input" {{$chef->is_active?'checked':''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{route('branch.kitchen.edit',[$chef['id']])}}"
                                            class="btn btn-outline-info btn-sm square-btn"
                                            title="{{translate('Edit')}}">
                                            <i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-outline-danger btn-sm square-btn form-alert" title="{{translate('Delete')}}" href="javascript:"
                                               data-id="chef-{{$chef['id']}}"
                                               data-message="{{translate('Want to delete this chef ?')}}">
                                                <i class="tio-delete"></i>
                                            </a>
                                        </div>
                                        <form action="{{route('branch.kitchen.delete',[$chef['id']])}}"
                                              method="post" id="chef-{{$chef['id']}}">
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
                            {{$chefs->links()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
    <script>
        $('.change-status').on('click', function(){
            let chefId = $(this).data('chef');
            let status = $(this).data('status');
            let url = "{{ route('branch.kitchen.status', [':chefId', ':status']) }}";
            url = url.replace(':chefId', chefId);
            url = url.replace(':status', status);
            location.href = url;
        });
    </script>

@endpush
