$(function() {
    $('input.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: new Date(infosys.con_start),
        maxDate: new Date(infosys.con_end)
    });
});
