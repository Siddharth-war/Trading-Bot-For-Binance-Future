"use strict";
$('.generate-code').click(function() {
    generateCode();
});

$('.openBtn').click(function() {
    modalShow(this);
});

$('#coupon_type').change(function() {
    const selectedValue = $(this).val();
    coupon_type_change(selectedValue);
});

$("#discount_type").change(function(){
    if(this.value === 'amount') {
        $("#max_discount_div").hide();
        $("#discount_label").text("Discount Amount");
        $("#discount_input").attr("placeholder", "Ex: 500")
    }
    else if(this.value === 'percent') {
        $("#max_discount_div").show();
        $("#discount_label").text("Discount Percent")
        $("#discount_input").attr("placeholder", "Ex: 50%")
    }
});

$(document).on('ready', function () {
    $('.js-flatpickr').each(function () {
        $.HSCore.components.HSFlatpickr.init($(this));
    });
});

function coupon_type_change(order_type) {
    if(order_type === 'first_order'){
        $('#limit-for-user').hide();
    }else{
        $('#limit-for-user').show();
    }
}
