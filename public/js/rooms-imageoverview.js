$(function () {
    var $table = $('#image-overview'),
        $children = $table.find('tr.child'),
        toggleChild = function () {
            var self = $(this),
                $child = self.next('tr'),
                close  = $child.hasClass('open');

            $children.fadeOut();
            $children.removeClass('open')

            if (!close) {
                $child.fadeIn();
                $child.addClass('open');
            }
        };

    $table.on('click.expand-row', 'tr.parent', toggleChild);
});
