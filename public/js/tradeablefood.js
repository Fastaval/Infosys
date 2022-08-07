$(function() {
    $('body').on('click', 'ul.tradeable-foodies', function() {
        $(this).toggleClass('opened').children().toggleClass('shown');
    });
});
