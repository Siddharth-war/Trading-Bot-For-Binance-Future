@extends('layouts.admin.app')

@section('title', translate('Add Group'))

@section('content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between gap-2 align-items-center mb-2">
                    <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                        <span class="page-header-title">{{ translate('Edit Group') }}</span>
                    </h2>

                </div>
                <div class="card-body">
                    <form id="groupForm" action="{{ route('admin.groups.update', $group) }}" method="POST">
                        @csrf @method('PUT')

                        <div class="form-group">
                            <label>Group Name  <small class="text-danger">* </small></label>
                            <input type="text" name="name" class="form-control" value="{{ $group->name }}" >
                            <span class="text-danger error-name"></span>
                        </div>
                        <div class="form-group">
                            <label class="input-label">Credit Limit <small class="text-danger">* </small></label>
                            <input type="number" name="credit_limit" class="form-control" placeholder="Please Enter Credit Limit" value="{{ $group->credit_limit }}" maxlength="8">
                            @error('credit_limit')
                            <span class="text-danger error-name">{{$message}}</span>
                            @enderror
                            {{-- <span class="text-danger error-name"></span> --}}
                        </div>

                        {{-- <div class="form-group">
                            <label class="input-label">Category <small class="text-danger">* </small></label>
                            <select type="text" name="category_id" class="form-control">
                                <option value="">Select Category</option>
                                @foreach ($categories as $cate)
                                <option value="{{ $cate->id }}" {{ $group->category_id == $cate->id ? 'selected' : ''}}>{{ $cate->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <span class="text-danger error-price">{{$message}}</span>
                            @enderror
                        </div>


                        <div class="form-group">
                            <label>Price Modifier  <small class="text-danger">* </small></label>
                            <input type="text" step="0.01" name="price_modifier" class="form-control" value="{{ $group->price_modifier }}">
                            <span class="text-danger error-price"></span>
                        </div> --}}
                        <div>
                            <button type="button"  class="btn btn-primary" id="add-more"><i class="tio-add"></i></button>

                        </div>
                        <div id="group-fields" >
                            @foreach($categories as $index=>$category)
                            <div  class="row group-row">

                                <div class=" form-group col-md-5" data-id="{{$index}}">
                                    <label class="input-label">Category <small class="text-danger">* </small></label>
                                    <input type="text" name="add[{{$index}}][categories]" placeholder="Enter Category" value="{{$category->name}}"  class="form-control" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label class="input-label">Price <small class="text-danger">* </small></label>
                                    <input type="number" name="add[{{$index}}][prices]" placeholder="Enter Price" value="{{$category?->price?->price}}"  class="form-control" required>
                                </div>
                                @if($index > 0)
                                <div class="form-group col-md-2 col-md-2 d-flex align-items-end">
                                    <button type="button" class="remove-field btn btn-danger" data-id="{{$index}}"><i class="tio-delete"></i></button>
                                </div>
                                @endif
                            </div>
                                @endforeach

                        </div>


                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('admin.groups.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>

<script>
$(document).ready(function() {
    $("#groupForm").on("submit", function(e) {
        $(".error-name, .error-price").text("");

        let name = $("input[name='name']").val();
        let price = $("input[name='price_modifier']").val();
        let errors = false;

        if (name.length < 3) {
            $(".error-name").text("Group name must be at least 3 characters.");
            errors = true;
        }

        if (price.length == 0) {
            $(".error-price").text("Price modifier must be between 0 and 10.");
            errors = true;
        }

        if (errors) e.preventDefault();
    });
});

$(document).ready(function() {
        let index = {{ count($categories) }}; // Start index based on existing categories

        $("#add-more").click(function () {
            let newRow = `
                <div class="group-row row mb-2" data-id="${index}">
                    <div class="form-group col-md-5">
                        <label class="input-label">Category <small class="text-danger">*</small></label>
                        <input type="text" name="add[${index}][categories]" placeholder="Enter Category" class="form-control" required>
                    </div>
                    <div class="form-group col-md-5">
                        <label class="input-label">Price <small class="text-danger">*</small></label>
                        <input type="number" name="add[${index}][prices]" placeholder="Enter Price" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="button" class="remove-field btn btn-danger" data-id="${index}"><i class="tio-delete"></i></button>
                    </div>
                </div>`;
            $("#group-fields").append(newRow);
            index++; // Increment index for new fields
        });

        $(document).on("click", ".remove-field", function () {
            console.log( $(this).closest(".group-row"))
            $(this).closest(".group-row").remove();
        });
});

</script>
@endsection
