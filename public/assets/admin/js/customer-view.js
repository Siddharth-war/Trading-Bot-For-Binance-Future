"use strict";

$(document).on('ready', function () {

    var datatable = $('.table').DataTable({
        "paging": false
    });

    $('#column1_search').on('keyup', function () {
        datatable
            .columns(1)
            .search(this.value)
            .draw();
    });

    $('#column3_search').on('change', function () {
        datatable
            .columns(2)
            .search(this.value)
            .draw();
    });
});
