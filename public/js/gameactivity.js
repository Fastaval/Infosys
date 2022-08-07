$(function() {
    var switched = 0;
    $('#stat-display-switch').click(function(e) {
        if (switched) {
            $('span.stat-display-old').each(function(idx, element) {
                $(element).show();
            });
            $('span.stat-display-new').each(function(idx, element) {
                $(element).hide();
            });
            --switched;
        } else {
            $('span.stat-display-old').each(function(idx, element) {
                $(element).hide();
            });
            $('span.stat-display-new').each(function(idx, element) {
                $(element).show();
            });
            ++switched;
        }
    });

    $('table.aktivitet-graph').on(
        'mouseover', 'p.aktivitet-graph-in-use', function() {
            var self = $(this),
                id = 'span-' + self.attr('id').replace(/p-/, '');

            self.find('#' + id).show();
        }
    ).on(
        'mouseout', 'p.aktivitet-graph-in-use', function() {
            var self = $(this),
                id = 'span-' + self.attr('id').replace(/p-/, '');

            self.find('#' + id).hide();
        }
    );
});
