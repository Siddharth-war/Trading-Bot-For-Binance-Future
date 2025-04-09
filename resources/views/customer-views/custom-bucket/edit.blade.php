@extends('layouts.customer.app')

@section('title', translate('Edit Custom Bucket'))

@section('content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3 mt-">
            <div class="card">
                <div class="card-header d-flex justify-content-between gap-2 align-items-center mb-2">
                    <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                        <span class="page-header-title">{{ translate('Edit Custom Bucket') }}</span>
                    </h2>

                </div>
                <div class="card-body">

                    <form id="groupForm" action="{{ route('customer.custom-bucket.bucket_store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{$bucket->id}}">
                        <div class="form-group">
                            <label class="input-label">Name <small class="text-danger">* </small></label>
                            <input type="text" name="name" value="{{$bucket->name}}" class="form-control" placeholder="Please Enter Name">
                            @error('name')
                            <span class="text-danger error-name">{{$message}}</span>

                            @enderror
                            <span class="text-danger error-name"></span>

                        </div>

                        <div id="product-container">
                            @foreach ($selected_products as $index=>$prod)
                            <div class="product-row">
                                <div class="row">

                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label class="input-label">Product <small class="text-danger">* </small></label>
                                            <select name="product[{{$index}}][product_id]" class="form-control">
                                                <option value="">Select Product</option>
                                                @foreach ($products as $product)
                                                <option value="{{ $product->id }}" {{ $prod->product_id == $product->id ? 'selected':''}}>
                                                    {{ $product->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="input-label">Quantity <small class="text-danger">* </small></label>
                                            <input type="number" name="product[{{$index}}][qty]" class="form-control" min="1" value="{{ $prod->qty ?? ''}}">
                                        </div>
                                    </div>
                                    @if($index > 0)
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger remove-row mt-5"><i class="tio-delete"></i></button>
                                    </div>
                                    @endif

                                </div>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" id="add-more" class="btn btn-primary">Add More</button>

                    </div>


                        <div class="d-flex justify-content-end gap-3 mb-2 mr-2">
                            <a href="{{ route('customer.custom-bucket.bucket_list') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="{{ asset('public/assets/admin/js/select2.min.js') }}"></script>

<script>
$(document).ready(function() {
    $("#groupForm").on("submit", function(e) {
        $(".error-name, .error-price").text("");

        let name = $("input[name='name']").val();
        let price = $("input[name='price_modifier']").val();
        let errors = false;

        if (name.length < 3) {
            $(".error-name").text("Group name is required.");
            errors = true;
        }

        if (price.length == 0) {
            $(".error-price").text("Price is required.");
            errors = true;
        }

        if (errors) e.preventDefault();
    });

    $('#product_id').select2();
});

</script>

<script>
    $(document).ready(function() {
        let rowIndex = {{ count($selected_products) }}; // Start index for dynamic fields

        $("#add-more").click(function() {
            let newRow = `
                <div class="product-row">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label class="input-label">Product <small class="text-danger">* </small></label>
                                <select name="product[${rowIndex}][product_id]" class="form-control">
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">Quantity <small class="text-danger">* </small></label>
                                <input type="number" name="product[${rowIndex}][qty]" class="form-control" min="1">
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-row mt-4"><i class="tio-delete"></i></button>
                        </div>
                    </div>
                </div>`;

            $("#product-container").append(newRow); // Append new row
            rowIndex++; // Increment index
        });

        // ✅ Fix: Use "on" for dynamically added elements
        $(document).on("click", ".remove-row", function() {
            if ($(".product-row").length > 1) {
                $(this).closest(".product-row").remove();
            } else {
                alert("At least one product row is required.");
            }
        });
    });
    </script>

</script>



@endsection
