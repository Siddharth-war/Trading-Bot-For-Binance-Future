@extends('layouts.admin.app')

@section('title', translate('Edit Day Delivery'))

@section('content')
<div class="content container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3 mt-">
            <div class="card">
                <div class="card-header d-flex justify-content-between gap-2 align-items-center mb-2">
                    <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                        <span class="page-header-title">{{ translate('Edit Day Delivery') }}</span>
                    </h2>

                </div>
                <div class="card-body">

                    <form id="groupForm" action="{{ route('admin.day_delivery_store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label class="input-label">Day <small class="text-danger">* </small></label>
                            <input type="hidden" name="day_id"  value="{{$days->id}}">
                            <select type="text" name="day_id" class="form-control" disabled>
                                <option value="">Please select day</option>
                                @foreach ($weekdays as $weekday)
                                <option value="{{$weekday->id}}" {{ $days->id == $weekday->id ? 'selected' : ''}}>{{$weekday->name}}</option>

                                @endforeach
                            </select>
                            @error('day')
                            <span class="text-danger error-name">{{$message}}</span>

                            @enderror

                        </div>
                        <div>
                            <button type="button"  class="btn btn-primary" id="add-more"><i class="tio-add"></i></button>

                        </div>
                        <div id="group-fields">
                            @if(!empty($days->location) && count($days->location) > 0)
                            @foreach ($days->location as $index=>$loc)

                            <div  class="row group-row">

                                <div class=" form-group col-md-10" >
                                    <label class="input-label">Location <small class="text-danger">* </small></label>
                                    <input type="text" name="location[]" value="{{$loc->location}}" placeholder="Enter location" class="form-control" required>
                                </div>
                                @if($index > 0)
                                <div class="form-group col-md-2 col-md-2 d-flex align-items-end">
                                    <button type="button" class="remove-field btn btn-danger" data-id="{{$index}}"><i class="tio-delete"></i></button>
                                </div>
                                @endif
                            </div>
                            @endforeach
                            @else
                            <div  class="row group-row">
                            <div class=" form-group col-md-10" >
                                <label class="input-label">Location <small class="text-danger">* </small></label>
                                <input type="text" name="location[]"  placeholder="Enter location" class="form-control" required>
                            </div>
                            </div>
                            @endif
                        </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mb-2 mr-2">
                            <a href="{{ route('admin.day_delivery_list') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit</button>
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
            $(".error-name").text("Group name is required.");
            errors = true;
        }

        if (price.length == 0) {
            $(".error-price").text("Price is required.");
            errors = true;
        }

        if (errors) e.preventDefault();
    });
});

$(document).ready(function() {


        $("#add-more").click(function () {
            let newRow = `
                <div class="group-row row mb-2">
                  <div class=" form-group col-md-10" >
                        <label class="input-label">Location <small class="text-danger">* </small></label>
                        <input type="text" name="location[]" placeholder="Enter location" class="form-control" required>
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="button" class="remove-field btn btn-danger"><i class="tio-delete"></i></button>
                    </div>
                </div>`;
            $("#group-fields").append(newRow);
            index++; // Increment index for new fields
        });

        $(document).on("click", ".remove-field", function () {
            $(this).closest(".group-row").remove();
        });
});
</script>
@endsection
