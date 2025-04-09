"use strict";
$("#date_from").on("change", function () {
    $('#date_to').attr('min',$(this).val());
});

$("#date_to").on("change", function () {
    $('#date_from').attr('max',$(this).val());
});

$(document).on('ready', function () {
    $('#bonus_type').on('change', function() {
        if($('#bonus_type').val() == 'amount')
        {
            $('#maximum_bonus_amount').removeAttr("required", true);
            $('#maximum_bonus_amount_div').addClass('d-none');
            $('#maximum_bonus_amount').val(null);
            $('#percentage').addClass('d-none');
            $('#currency_symbol').removeClass('d-none');
        }
        else
        {
            $('#maximum_bonus_amount').removeAttr("required");
            $('#maximum_bonus_amount_div').removeClass('d-none');
            $('#percentage').removeClass('d-none');
            $('#currency_symbol').addClass('d-none');
        }
    });
});
