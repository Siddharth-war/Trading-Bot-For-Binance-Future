"use strict";

$(document).ready(function() {

    $(".select-all-associate").on('change', function (){
        if ($(".select-all-associate:checked").length == $(".select-all-associate").length) {
            $("#select-all-btn").prop("checked", true);
        } else {
            $("#select-all-btn").prop("checked", false);
        }
    });

    $("#select-all-btn").on('change', function (){
        if ($("#select-all-btn").is(":checked")) {
            $(".select-all-associate").prop("checked", true);
        } else {
            $(".select-all-associate").prop("checked", false);
        }
    });

    if ($(".select-all-associate:checked").length == $(".select-all-associate").length) {
        $("#select-all-btn").prop("checked", true);
    }
});
