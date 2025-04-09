$(function() {
    $('#banner_type').change(function(){
        if ($(this).val() === 'video'){
            $('#video_section').show();
            $('#image_section').hide();
        }else{
            $('#video_section').hide();
            $('#image_section').show();
        }
    });
});

function readURL(input, viewer_id) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#'+viewer_id).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$("#customFileEg").change(function () {
    readURL(this, 'viewer');
});
