$(function() {
    var new_table_definition = '<table class="data-table"><thead><tr><th>Id</th><th>Navn</th></tr></thead><tbody></tbody></table>',
        datatables_settings = {
            bJQueryUI: true,
            sPaginationType: 'full_numbers',
            bProcessing: true,
            bLengthChange: true,
            bServerSide: true,
            sAjaxSource: window.infosys_data.all_users_ajax,
            iDisplayStart: 0,
            iDisplayLength: 25,
            aLengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            aaSorting: [[0, 'asc']],
            fnServerParams: function(datamap) {
                var extra_columns = [];
                if (active_columns && active_columns.length) {
                    active_columns.each(function(idx, item) {
                        extra_columns.push($(item).attr('id'));
                    });
                }
                datamap.push({name: 'extra_columns', value: extra_columns});

                if (no_search_term) {
                    datamap.push({name: 'no_search_term', value: no_search_term});
                }
            }
        },
        no_search_term = false,
        search_dialog = $('form.deltager-search-box'),
        datatables_settings_clone,
        active_columns;

    function activateUsedFields(sent_data) {
        'use strict';
        var prop    = null,
            element = null;

        for (prop in sent_data) {
            if (sent_data.hasOwnProperty(prop)) {
                element = $('#' + prop.replace(/^.*\[([^\]]+)\].*/, '$1'));

                if (element.length && element.hasClass('addable-column')) {
                    element.addClass('active');
                }
            }
        }
    }

    function rebuildList() {
        var new_table  = $(new_table_definition),
            header_row = new_table.find('thead tr');

        active_columns = $('.available-columns .addable-column.active');
        active_columns.each(function(idx, item) {
            header_row.append('<th>' + $(item).text() + '</th>');
        });

        $('.dataTables_wrapper').replaceWith(new_table);
        new_table.addClass('current');
        $('.data-table').dataTable(datatables_settings);
    }

    $('.more-columns').click(function() {
        $('.available-columns').slideToggle();
    });

    $('.reset-columns').click(function() {
        $('.available-columns').slideToggle();
        $('.addable-column.active').removeClass('active');
        rebuildList();
    });

    $('.reset-search img').click(function() {
        no_search_term = true;
        rebuildList();
        $(this).closest('span').remove();
        no_search_term = true;
    });

    $('.available-columns').on('click', '.addable-column', function() {
        var self = $(this);

        if (self.hasClass('active')) {
            self.removeClass('active');
        } else {
            self.addClass('active');
        }

        rebuildList();
    });

    datatables_settings_clone = $.extend(true, {}, datatables_settings);
    datatables_settings_clone.iDeferLoading = window.infosys_data.initial_rows;
    $('.data-table').dataTable(datatables_settings_clone);

    $('a.search').click(function() {
        search_dialog.dialog('open');
    });

    search_dialog.dialog({
        modal: true,
        width: 'auto',
        autoOpen: document.location.href.search(/show_search_box=true/) != -1
    }).on('click', 'button', function(e) {
        var data = {};

        e.preventDefault();

        $('div.deltager-search-box-inner input, div.deltager-search-box-inner select').each(function() {
            var self = $(this);

            if (self.attr('type') == 'checkbox' && !self.is(':checked')) {
                return;
            }

            if (self.val()) {
                data[self.attr('name')] = self.val();
            }
        });

        $.ajax({
            url: search_dialog.attr('action'),
            method: 'GET',
            data: data,
            success: function() {
                activateUsedFields(data);
                search_dialog.dialog('close');
                rebuildList();
            }, error: function(jqXHR) {
            }
        });
    });;

    $('a.character-sheet').click(function(e) {
        var range   = [],
            address = $(this).attr('href');

        e.preventDefault();

        $('table.data-table tbody tr').each(function() {
            range.push($(this).children().first().text());
        });

        window.open(address.replace(/id_range/, range.join('-')));
    });

    $('.id-cards').click(function(e) {
        var range   = [],
            address = $(this).attr('href');

        e.preventDefault();

        $('table.data-table tbody tr').each(function() {
            range.push($(this).children().first().text());
        });

        window.open(address.replace(/id_range/, range.join('-')));
    });
});
