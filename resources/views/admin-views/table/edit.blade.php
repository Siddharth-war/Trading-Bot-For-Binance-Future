@extends('layouts.admin.app')

@section('title', translate('Update table'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('public/assets/admin/img/icons/table.png')}}" alt="">
                <span class="page-header-title">
                    {{translate('Update_Table')}}
                </span>
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.table.update',[$table['id']])}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="number">{{translate('Table_Number')}}
                                <span class="text-danger">*</span></label>
                                <input type="number" name="number" class="form-control" id="number"
                                    placeholder="{{translate('Ex')}} : {{translate('1')}}" value="{{$table->number}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity">{{translate('Table_Capacity')}}
                                <span class="text-danger">*</span></label>
                                <input type="number" name="capacity" class="form-control" id="capacity" min="1" max="99"
                                    placeholder="{{translate('Ex')}} : {{translate('4')}}" value="{{$table->capacity}}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('Select_Branch')}}
                                <span class="text-danger">*</span></label>
                                <select name="branch_id" class="custom-select" required>
                                    <option disabled selected>{{ translate('--select_Branch--') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{$branch['id']}}" {{ $table->branch_id == $branch->id ? 'selected' : '' }}>{{$branch['name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection


