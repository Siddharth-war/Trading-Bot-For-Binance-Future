@extends('layouts.admin.app')

@section('title', translate('Add new table'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/table.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Add_New_Table')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <form action="{{route('admin.table.store')}}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="number">{{translate('Table_Number')}} <span class="text-danger">*</span></label>
                                        <input type="number" name="number" class="form-control" id="number"
                                            placeholder="{{translate('Ex')}} : {{translate('1')}}" value="{{old('number')}}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">{{translate('Table Capacity')}} <span class="text-danger">*</span></label>
                                        <input type="number" name="capacity" class="form-control" id="capacity"
                                            placeholder="{{translate('Ex')}} : {{translate('4')}}" min="1" max="99" value="{{old('capacity')}}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlSelect1">{{translate('Select_Branch')}} <span class="text-danger">*</span></label>
                                        <select name="branch_id" class="custom-select" required>
                                            <option value="" selected>{{ translate('--select--') }}</option>
                                            @foreach($branches as $branch)
                                                <option value="{{$branch['id']}}">{{$branch['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
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
                                <h5 class="d-flex gap-2">
                                    {{translate('Table_List')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{$tables->total()}}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <form action="{{url()->current()}}" method="GET">
                                    <div class="input-group">
                                        <input id="datatableSearch_" type="search" name="search" class="form-control" placeholder="{{translate('Search by Table Number')}}" aria-label="Search" value="{{$search}}" required="" autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
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
                                    <th>{{translate('Table Number')}}</th>
                                    <th>{{translate('Table Capacity')}}</th>
                                    <th>{{translate('Branch')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th class="text-center">{{translate('action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tables as $k=>$table)
                                    <tr>
                                        <th scope="row">{{$tables->firstitem()+$k}}</th>
                                        <td>{{$table['number']}}</td>
                                        <td>{{$table['capacity']}}</td>
                                        <td class="{{ $table?->branch?->name? '' : 'text-danger' }}">{{$table?->branch?->name ?? translate('Branch Deleted')}}</td>
                                        <td>
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher_input redirect-url"
                                                       data-url="{{route('admin.table.status',[$table['id'],$table->is_active?0:1])}}"
                                                        class="toggle-switch-input" {{$table->is_active?'checked':''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-3">
                                                <a href="{{route('admin.table.update',[$table['id']])}}"
                                                    class="btn btn-outline-info btn-sm square-btn"
                                                    title="{{translate('Edit')}}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm square-btn form-alert" title="{{translate('Delete')}}"
                                                        data-id="table-{{$table['id']}}"
                                                        data-message="{{translate('Want to delete this table ?')}}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form action="{{route('admin.table.delete',[$table['id']])}}"
                                                    method="post" id="table-{{$table['id']}}">
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
                                {{$tables->links()}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


