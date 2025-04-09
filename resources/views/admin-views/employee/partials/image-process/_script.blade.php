<script src="{{asset('public/assets/back-end/js/croppie.js')}}"></script>
<script>
    "use strict";

    function Validate(file) {
        var x;
        var le = file.length;
        var poin = file.lastIndexOf(".");
        var accu1 = file.substring(poin, le);
        var accu = accu1.toLowerCase();
        if ((accu != '.png') && (accu != '.jpg') && (accu != '.jpeg')) {
            x = 1;
            return x;
        } else {
            x = 0;
            return x;
        }
    }

    function cropView(id) {
        $("#crop-" + id).show();
    }

    function removeImage(route, id) {
        $(function () {
            $.ajax({
                type: 'get',
                url: route,
                dataType: 'json',
                success: function (data) {
                    if (data.success === 1) {
                        $("#img-suc-" + id).hide();
                        $("#img-err-" + id).hide();
                        $("#crop-" + id).hide();
                        $("#show-images-" + id).html(data.images);
                        $("#image-count-" + id).text('( ' + data.count + ' )');
                    } else if (data.success === 0) {
                        $("#img-suc-" + id).hide();
                        $("#img-err-" + id).show();
                    }
                },
            });
        });
    }

    var resize_{{str_replace('-','_',$id)}} = $('#upload-image-div-{{$id}}').croppie({
        enableExif: true,
        enableOrientation: true,
        viewport: {
            width: '{{$width}}',
            height: '{{$height}}',
            type: 'square'
        },
        boundary: {
            width: '{{$width+10}}',
            height: '{{$height+10}}',
        }
    });

    $('#image-set-{{$id}}').on('change', function () {

        var file = $('#image-set-{{$id}}').val();
        var file1 = Validate(file);
        if (file1 == 1) {
            $("#crop-{{$id}}").hide();
            $(this).val('');
            toastr.error('{{translate('This is not an image file')}}.', {
                CloseButton: true,
                ProgressBar: true
            });
        } else {
            $("#crop-{{$id}}").show();
            $('.image-set-{{$id}}').hide();
            $('.btn-upload-image-{{$id}}').show();
            var reader_{{str_replace('-','_',$id)}} = new FileReader();
            reader_{{str_replace('-','_',$id)}}.onload = function (e) {
                resize_{{str_replace('-','_',$id)}}.croppie('bind', {
                    url: e.target.result
                }).then(function () {
                });
            }
            reader_{{str_replace('-','_',$id)}}.readAsDataURL(this.files[0]);
        }

    });

    $('.btn-upload-image-{{$id}}').on('click', function (e) {
        e.preventDefault();
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        resize_{{str_replace('-','_',$id)}}.croppie('result', {
            type: 'canvas',
            size: 'viewport'
        }).then(function (img) {
            $.ajax({
                type: 'post',
                url: '{{$route}}',
                data: {
                    "image": img,
                    "folder": '{{str_replace('-','_',$id)}}',
                    "multi_image": '{{$multi_image}}'
                },
                dataType: 'JSON',
                beforeSend: function () {
                    $("#loading").show();
                },
                success: function (data) {
                    if (data.success === 1) {
                        $("#img-suc-{{$id}}").show();
                        $("#img-err-{{$id}}").hide();
                        $("#crop-{{$id}}").hide();
                        $(".btn-upload-image-{{$id}}").hide();
                        $("#show-images-{{$id}}").html(data.images);
                        $("#image-count-{{$id}}").text(data.count + ' ' + 'image selected.');
                    } else if (data.success === 0) {
                        $("#img-suc-{{$id}}").hide();
                        $("#img-err-{{$id}}").show();
                        $(".btn-upload-image-{{$id}}").hide();
                    }
                },
                error: function () {
                    $("#img-suc-{{$id}}").hide();
                    $("#img-err-{{$id}}").show();
                },
                complete: function () {
                    $("#loading").hide();
                    $("#loading-{{$id}}").hide();
                },
            });
        });
    });

</script>
