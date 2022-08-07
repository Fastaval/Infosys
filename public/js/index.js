$(function() {
    $('.graph-link').click(function(e) {
        var self         = $(this),
            graph_dialog = $('<div></div>'),
            chart_div    = $('<div id="chart_div"></div>'),
            json;

        e.preventDefault();

        graph_dialog.append(chart_div);

        $.ajax({
            url: self.attr('href'),
            success: function(data) {
                graph_dialog.dialog({
                    modal: true,
                    height: 400,
                    width: 700,
                    close: function() {
                        graph_dialog.remove();
                    }
                }).append(chart_div);

                json = $.parseJSON(data);

                common.makeChart(chart_div[0], json.chart_data, json.chart_config);
            },
            error: function(jqXHR) {
                graph_dialog.remove();
                alert(jqXHR.responseText);
            }
        });
    });


});
