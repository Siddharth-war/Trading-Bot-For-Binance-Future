<style>
    input[type="file"] {
        display: none;
    }

    .custom-file-upload {
        margin-left: 38%;
        border: 1px solid #ccc;
        display: inline-block;
        padding: 6px 12px;
        cursor: pointer;
    }
</style>

@if(!isset($width))
    @php($width=516)
@endif

@if(!isset($margin_left))
    @php($margin_left='0%')
@endif

<div class="modal fade" id="{{$modal_id}}" tabindex="-1" role="dialog" aria-labelledby=""
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="width: {{$width+66}}px;margin-left: {{$margin_left}}">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize" id="">{{str_replace('-',' ',$modal_id)}}</h5>
            </div>
            <div class="modal-body">

                <div class="alert alert-block alert-success d-none" id="img-suc-{{$modal_id}}">
                    <i class="ace-icon fa fa-check green"></i>
                    <strong class="green">
                        {{translate('Image uploaded successfully')}}.
                    </strong>
                </div>

                <div class="alert alert-block alert-danger d-none" id="img-err-{{$modal_id}}">
                    <strong class="red">
                        {{translate('Error , Something went wrong')}} !
                    </strong>
                </div>

                <div class="row" id="show-images-{{$modal_id}}">
                    @include('admin-views.employee.partials.image-process._show-images',['folder'=>str_replace('-','_',$modal_id)])
                </div>

                <form>
                    <div class="form-group d-none"id="crop-{{$modal_id}}">
                        <div id="upload-image-div-{{$modal_id}}"></div>
                    </div>
                    <div class="form-group" id="select-img-{{$modal_id}}">
                        <label for="image-set-{{$modal_id}}" class="custom-file-upload">
                            {{translate('Choose Image')}} <i class="fa fa-plus-circle"></i>
                        </label>
                        <input type="file" class="image-set" name="image" data-id="{{$modal_id}}"
                               id="image-set-{{$modal_id}}" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{translate('Close')}}
                </button>
                <button type="button" class="d-none btn btn-primary btn-upload-image-{{$modal_id}}">
                    {{translate('Add')}}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    "use strict";

    $('.image-set').change(function() {
        var modalId = $(this).data('id');
        cropView(modalId);
    });
</script>
