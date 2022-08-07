
(function ($) {
    'use strict';

    var $stock_table = $('#shop-overview-table'),
        $body = $('body'),
        $current_editable = null,
        $dialog = null,
        product_template = $('#product-template').text(),
        cancelCurrentCell = function (update_value) {
            var value;

            if ($current_editable) {
                $current_editable.removeClass('editing');

                value = update_value ? $current_editable.find('input').val() : $current_editable.attr('data-original');

                $current_editable.text(value);

                $current_editable.off('keyup');

                $current_editable = null;
            }
        },
        isStockValid = function ($cell) {
            return parseInt($cell.attr('data-original'), 10) >= parseInt($cell.find('input').val(), 10);
        },
        isSalesValid = function ($cell) {
            return parseInt($cell.attr('data-original'), 10) <= parseInt($cell.find('input').val(), 10);
        },
        isValid = function ($cell) {
            if ($cell.hasClass('stock-validation')) {
                return isStockValid($cell);
            }

            if ($cell.hasClass('sales-validation')) {
                return isSalesValid($cell);
            }

            return true;
        },
        persistCurrentCell = function () {
            // handle validation
            if (!isValid($current_editable)) {
                return;
            }

            $.ajax({
                url: window.single_update_url,
                type: 'POST',
                data: {id: $current_editable.parent().attr('data-id'), type: $current_editable.attr('data-class'), value: $current_editable.find('input').val()},
                success: function () {
                    cancelCurrentCell(true);
                },
                error: function () {
                    window.alert('Kunne ikke opdatere');
                }
            });

        },
        handleEditableKeypress = function (e) {
            if (e.keyCode) {
                if (e.keyCode === 27) {
                    cancelCurrentCell();
                    return;
                }

                if (e.keyCode === 13) {
                    persistCurrentCell();
                }
            }
        },
        cancelEditingHandler = function () {
            cancelCurrentCell();
        },
        convertCellToEditable = function ($cell) {
            var value = parseFloat($cell.text());

            $cell.attr('data-original', value);
            $cell.html('<input type="text" required pattern="^-?[0-9]+(,[0-9]{1,2})?" value="' + value + '"/>');
            $cell.addClass('editing');

            $cell.find('input').focus();
        },
        setEditableHandler = function ($cell) {
            $cell.on('keyup', handleEditableKeypress);
        },
        handleStockIncrease = function ($row, new_value) {
            var $cell = $row.find('td[data-class="stock"]');

            $.ajax({
                url: window.single_update_url,
                type: 'POST',
                data: {id: $row.attr('data-id'), type: 'stock', value: new_value},
                success: function () {
                    $cell.text(new_value);
                    $dialog.remove();
                    $dialog = null;
                },
                error: function () {
                    window.alert('Kunne ikke opdatere');
                }
            });
        },
        increaseStockHandler = function () {
            var $row = $(this).closest('tr');

            if ($dialog) {
                $dialog.remove();
            }

            $dialog = $('<div><p>Angiv ny beholdning.</p><p><input value="' + $row.find('td[data-class="stock"]').text() + '" type="number"/></p><button class="upload">Upload</button></div>');

            $dialog.appendTo('body');

            $dialog.dialog({
                modal: true,
                width: '20em',
                close: function () {
                    $dialog.remove();
                    $dialog = null;
                }
            });

            $dialog.on('click', 'button.upload', function () {
                handleStockIncrease($row, $dialog.find('input').val());
            });
        },
        editableHandler = function (e) {
            var self = $(this);

            if (self.hasClass('editing')) {
                e.stopPropagation();

                return;
            }

            cancelCurrentCell();

            $current_editable = self;

            // convert cell
            convertCellToEditable(self);
            // set handler
            setEditableHandler(self);

            e.stopPropagation();
        },
        updateTable = function (parsed_data) {
            var $tbody = $stock_table.find('tbody');

            $tbody.children().remove();

            parsed_data.forEach(function (product) {
                var text;

                text = product_template.replace(/:product-id:/, product.id)
                    .replace(/:name:/, product.name)
                    .replace(/:code:/, product.code)
                    .replace(/:cost:/, product.cost)
                    .replace(/:price:/, product.price)
                    .replace(/:stock:/, product.stock)
                    .replace(/:sold:/, product.sold);

                $tbody.append(text);
            });

        },
        deleteHandler = function () {
            var self = $(this);

            if (!window.confirm('Er du sikker på du vil slette produktet? No undo!')) {
                return;
            }

            $.ajax({
                url: window.delete_product_url,
                type: 'POST',
                data: {id: self.closest('tr').attr('data-id')},
                success: function () {
                    self.closest('tr').remove();
                },
                error: function () {
                    window.alert('Kunne ikke slette produktet');
                }
            });
        },
        handleUpload = function () {
            var data = {
                    input: $(this).parent().find('textarea').val()
                };

            $.ajax({
                url: window.parse_spreadsheet_url,
                type: 'POST',
                data: data,
                success: function (parsed_data) {
                    updateTable(parsed_data);
                    $dialog.dialog('close');
                },
                error: function () {
                    window.alert('Kunne ikke parse data');
                }
            });
        },
        handleUploadSpreadsheet = function () {
            if ($dialog) {
                $dialog.remove();
            }

            $dialog = $('<div><p>Copypaste spreadsheet data ind her. Kolonnerne skal have overskrifter magen til dem i tabellen for at upload accepteres.</p><textarea class="spreadsheet-upload"></textarea><button class="upload">Upload</button></div>');

            $dialog.appendTo('body');

            $dialog.dialog({
                modal: true,
                width: '70em',
                close: function () {
                    $dialog.remove();
                    $dialog = null;
                }
            });

            $dialog.on('click', 'button.upload', handleUpload);
        },
        displayProductGraphHandler = function () {
            var self    = $(this),
                $row    = self.closest('tr'),
                product = $row.find('td.product-name').text(),
                $chart,
                handleGraphData = function(data) {
                    $dialog = $('<div><h2>' + product + '</h2><div id="chart-div"></div></div>');

                    $dialog.appendTo($('body'));

                    $chart = $dialog.find('#chart-div');

                    $dialog.dialog({
                        modal: true,
                        width: 700,
                        height: 400,
                        close: function () {
                            $dialog.remove();
                            $dialog = null;
                        }
                    });

                    common.makeChart($chart[0], data.chart_data, data.chart_config);
                };

            if ($dialog) {
                $dialog.remove();
            }

            $.ajax({
                url: window.graph_data_url.replace(/:id:/, $row.data('id')),
                success: handleGraphData,
                error: function(jqXHR) {
                    $dialog.remove();
                    alert(jqXHR.responseText);
                }
            });
        };

    $('#upload-spreadsheet-data').click(handleUploadSpreadsheet);
    $stock_table.on('click.edit', '.editable', editableHandler);
    $stock_table.on('click.delete', '.delete', deleteHandler);
    $stock_table.on('click.increase-stock', '.increase-stock img', increaseStockHandler);
    $stock_table.on('click.display-product-graph', 'img', displayProductGraphHandler);
    $(document).click(cancelEditingHandler);
}(jQuery));
