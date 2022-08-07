/**
 * Copyright (C) 2009-2012 Peter Lind
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/gpl.html>.
 *
 * PHP version 5
 *
 * @category  Infosys
 * @package   Javascript
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

var gds_object = {
    public_uri: null,

    fetching_suggestions: false,

    flipInactiveRows: function(e) {
        var self     = $(this),
            table    = self.closest('div.gds-popuptable'),
            shift_id = self.closest('span.gds-shift-popupdetails').attr('id').replace(/[^0-9]/g, '');

        if (self.attr('checked')) {
            if (!confirm('Er du sikker på du vil override de normale GDS-indstilinger?')) {
                self.attr('checked', false);
                return;
            }

            table.find('input.gds-busy-box').show();
        } else {
            table.find('input.gds-busy-box').hide();
        }
    },

    getCheckedRows: function(element) {
        var table     = element.parent().find('table'),
            inputs    = table.find('input'),
            to_remove = [],
            length    = inputs.length;

        inputs.each(function(idx, item) {
            if (item.checked) {
                item.checked = false;
                to_remove.push(item);
            }
        });

        return to_remove;
    },

    getOverrideState: function(element) {
        return element.closest('.gds-shift-popupdetails').find('input.gds-override').attr('checked');
    },

    handleAjaxSearch: function(e){
        var self     = $(this),
            term     = self.closest('div.gds-popuptable').find('input.gds-search-box').val().replace(/ /, '_'),
            shift_id = self.parent().parent().attr('id').replace(/[^0-9]/g, '');

        e.preventDefault();

        if (!term) {
            return;
        }

        $.ajax({
            url: gds_object.public_uri + 'deltager/ajaxsearch/' + shift_id + '/' + term,
            type: 'GET',
            success: function(data) {
                gds_object.ajaxSearchCallback(shift_id, $.parseJSON(data));
            }
        });
    },

    ajaxSearchCallback: function(id, search_result){
        var table = $('#gds-shift-details-' + id).find('.gds-search-table');
        table.find('tbody').html('');
        this.fillSignupTableCallback(table, search_result, 'from-search');
    },

    handleShiftLinkClick: function(e){
        var self = $(this),
            id   = self.attr('id').replace(/[^0-9]*/g, '');

        e.preventDefault();

        gds_object.normaliseShiftLinks();
        $('span.gds-shift-popupdetails').each(function(idx, item) {
            gds_object.hideDetailPopups($(item), id);
        });

        if (!$('#gds-shift-details-' + id).is(':visible')) {
            self.css('font-weight', 'bold').prev('span').show();
            gds_object.fillSignupTable(id);
        } else {
            $('#gds-shift-details-' + id).hide();
        }
    },

    normaliseShiftLinks: function() {
        $('a.gds-shift-link').css('font-weight', 'normal');
    },

    hideDetailPopups: function(element, id) {
        if (!id || element.attr('id') != ('gds-shift-details-' + id)) {
            element.hide();
        }
    },

    checkBoxesToIdString: function(checked) {
        var id_string = '',
            length    = checked.length;

        $(checked).each(function(idx, item) {
            id_string += $(item).attr('class').replace(/[^0-9]/g, '') + '-';
        });

        return id_string.substr(0, (id_string.length - 1));
    },

    handleRemoveParticipants: function(e) {
        var self      = $(this),
            checked   = gds_object.getCheckedRows(self),
            shift_id  = self.closest('span.gds-shift-popupdetails').attr('id').replace(/[^0-9]/g, ''),
            id_string = gds_object.checkBoxesToIdString(checked);

        e.preventDefault();

        $.ajax({
            url: gds_object.public_uri + 'gds/ajaxremovefromshift/' + shift_id + '/' + id_string,
            type: 'GET',
            success: function(transport) {
                if (transport == 'worked') {
                    gds_object.removeParticipantsCallback(self, checked);
                } else {
                    if (id_string == '') {
                        alert('Du har ikke krydset nogen deltager af, som skal fjernes.');
                    } else {
                        alert('Kunne ikke fjerne deltagere fra vagten.');
                    }
                }
            }
        });
    },

    removeParticipantsCallback: function(self, checked) {
        var removed          = gds_object.removeRowFromTable(checked),
            for_signup_table = [];

        $(removed).each(function(idx, item) {
            var tab,
                shift = self.closest('span.gds-shift-popupdetails');

            if (item.hasClass('from-search')) {
                tab = shift.find('.gds-search-table tbody');
                tab.append(item);
            } else if (item.hasClass('gds-signup-row')) {
                for_signup_table.push(item);
            }
        });

        gds_object.addToSignup(self, for_signup_table);
        gds_object.reLayoutTables(self);
        gds_object.updateLiveCount(self);
    },

    updateLiveCount: function(self) {
        var container    = self.closest('span.gds-shift-popupdetails'),
            participants = container.find('table.gds-participant-table tbody tr').length,
            shift_id     = container.attr('id').replace(/[^0-9]/g, '');

        $('#gds-shift-live-count-' + shift_id).text(participants);
    },

    handleAddSignups: function(e) {
        var self      = $(this),
            checked   = gds_object.getCheckedRows(self),
            id_string = gds_object.checkBoxesToIdString(checked),
            shift_id  = self.closest('span.gds-shift-popupdetails').attr('id').replace(/[^0-9]/g, '');

        e.preventDefault();

        $.ajax({
            url: gds_object.public_uri + 'gds/ajaxaddtoshift/'+ shift_id +'/' + id_string,
            type: 'GET',
            success: function(transport) {
                if (transport == 'worked') {
                    gds_object.signupPeopleCallback(self, checked);
                } else {
                    if (id_string == '') {
                        alert('Du har ikke krydset nogen deltager af, som skal tilføjes.');
                    } else {
                        alert('Kunne ikke tilføje deltagere til vagten.');
                    }
                }
            }
        });
    },

    signupPeopleCallback: function(self, checked) {
        var to_add = this.removeRowFromTable(checked);
        gds_object.addToShift(self, to_add);
        gds_object.reLayoutTables(self);
        gds_object.updateLiveCount(self);
    },

    reLayoutTables: function(self) {
        self.closest('div.gds-popuptable').each(function(idx, item) {
            var rows   = $(item).find('tbody tr'),
                ii     = 0,
                length = rows.length;

            for (; ii < length; ++ii) {
                if (!(ii % 2) && !$(rows[ii]).hasClass('gds-altern')) {
                    $(rows[ii]).addClass('gds-altern');
                } else if ((ii % 2) && $(rows[ii]).hasClass('gds-altern')) {
                    $(rows[ii]).removeClass('gds-altern');
                }
            }
        });
    },

    handleAddRandoms: function(e) {
        var eve = this.getSource(e);
        var to_add = this.removeRowFromTable(this.getCheckedRows(eve));
        gds_object.addToShift(eve,to_add);
        Event.stop(eve.e);
    },

    addToShift: function(self, to_add) {
        var container = self.closest('div.gds-popuptable'),
            tbody     = container.find('table.gds-participant-table tbody');

        $(to_add).each(function(idx, item) {
            $(item).find('td.noshow').removeClass('hidden');
            tbody.append(item);
        });
    },

    addToSignup: function(self, to_add){
        var table = self.closest('span.gds-shift-popupdetails').find('table.gds-signup-table tbody');

        $(to_add).each(function(idx, item) {
            $(item).find('td.noshow').addClass('hidden');
            table.append(item);
        });
    },

    removeRowFromTable: function(checked) {
        var removed = [],
            length  = checked.length;

        $(checked).each(function(idx, item) {
            removed.push($(item).closest('tr').remove());
        });

        return removed;
    },

    handleCategoryLinkClick: function(e){
        var that = this,
            id   = $(this).attr('id').replace(/^.*-(\d+|all)$/, '$1');

        e.preventDefault();

        $('span.gds-shift-popupdetails').hide();

        if (id == 'all') {
            $('tr.gds-shift-row').show();
        } else {
            $('tr.gds-shift-row').each(function(idx, item) {
                var row = $(item);
                if (row.hasClass('gds_' + id)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
    },

    fillSignupTable: function(id){
        var that = gds_object,
            table = $('#gds-shift-details-' + id).find('.gds-signup-table');

        table.find('tbody').html('');
        $.ajax({
            url: gds_object.public_uri + 'gds/ajaxgetsignups/' + id,
            type: 'GET',
            success: function(data) {
                that.fillSignupTableCallback(table, $.parseJSON(data), 'gds-signup-row');
            }
        });
    },

    fillSignupTableCallback: function(table, json_array, row_class){
        var override    = this.getOverrideState(table),
            json_length = json_array.length,
            tbody       = table.find('tbody'),
            shift_id    = table.closest('div.gds-popuptable').data('shift'),
            row;

        for (var i = 0; i < json_length; ++i) {
            row = this.createRow(json_array[i], override, shift_id);
            row.addClass(row_class);
            tbody.append(row);

            if (!(i % 2)) {
                row.addClass('gds-altern');
            }
        }
    },

    createRow: function(user, override, shift_id){
        var row = $('<tr data-name="' + user.navn + '" data-email="' + user.email + '" data-note="' + user.note + '" data-medical-note="' + user.medical_note + '" data-id="' + user.id + '" data-phone="' + user.mobil + '" data-age="' + user.age + '" data-gamemaster="' + (user.isGamemaster ? 'yes' : 'no') + '" data-assigned-shifts="' + user.assignedShifts + '"><td class="gds-checkbox-cell"><input type="checkbox"/></td><td class="name"><a href="' + gds_object.public_uri + 'deltager/visdeltager/' + user.id + '">' + user.navn + '</a></td><td>' + user.mobil + '</td><td class="hidden noshow"><input type="checkbox" class="no-show" data-participant="' + user.id + '" data-shift="' + shift_id + '"/></td></tr>'),
            box;

        if (user.disabled == 'true' || user.maxshifts =='true') {
            row.find('td.name').addClass('gds-isbusy');
        }

        if (user.disabled == 'false') {
            box = row.find('input');
            box.addClass('gds-remove-participant-' + user.id);

            if (user.maxshifts == 'true') {
                box.addClass('gds-busy-box');

                if (!override) {
                    box.hide();
                }

            }

        }

        if (user.assignedShifts > 0) {
            row.addClass('gds-already-assigned');
        }

        if (user.isGamemaster) {
            row.addClass('gds-is-gamemaster');
        }

        return row;
    },

    getSource: function(e){
        var elem = e || window.event;
        return {'target': elem.target || elem.srcElement, 'e': elem};
    },

    handleShowRemainingSuggestionsEvent: function(event) {
        var self = $(this);

        self.siblings('.hidden')
            .removeClass('hidden')
            .end()
            .remove();
    },

    handleSearchShiftParticipants: function (event) {
        var url = gds_object.shift_search_uri.replace(/:shift_id:/, this.getAttribute('data-shift-id'));

        window.open(url);
    },

    handleAssignToShiftEvent: function(event) {
        var self           = $(this),
            span           = self.siblings('span'),
            participant_id = self.closest('tr').data('participant'),
            shift_id       = self.closest('div.gds-popuptable').data('shift');

        self.toggleClass('icon-time icon-ok assign-to-shift');

        $.ajax({
            url: gds_object.public_uri + 'gds/ajaxaddtoshift/'+ shift_id +'/' + participant_id,
            type: 'GET',
            success: function(data) {
                if (data == 'worked') {
                    gds_object.assignSuggestionToShift(self.closest('tr'), participant_id, shift_id);
                } else {
                    alert('Kunne ikke tilføje deltagere til vagten.');
                }
            }
        });
    },

    assignSuggestionToShift: function(row, participant_id, shift_id) {
        var table = row.closest('div.gds-popuptable').find('table.gds-participant-table'),
            cells = row.find('td'),
            user  = {
                id: participant_id,
                navn: cells.eq(1).text(),
                mobil: cells.eq(2).text(),
                disabled: "false"
            },
            new_row = this.createRow(user, false, shift_id);

        new_row.find('.hidden')
            .removeClass('hidden')
            .end()
            .appendTo(table.find('tbody'));

        row.remove();
    },

    handleMarkContactedEvent: function(event) {
        var self           = $(this),
            span           = self.siblings('span'),
            participant_id = self.closest('tr').data('participant');

        self.toggleClass('icon-time icon-plus-sign mark-contacted');

        $.ajax({
            url: gds_object.public_uri + 'gds/ajax-mark-contacted',
            type: 'POST',
            data: {participant_id: participant_id},
            success: function() {
                span.text(parseInt(span.text(), 10) + 1);
            },
            complete: function() {
                self.toggleClass('icon-time icon-plus-sign mark-contacted');
            }
        });
    },

    handleFindSuggestionsEvent: function(event) {
        var self       = $(this),
            popuptable = self.closest('div.gds-popuptable'),
            shift_id   = popuptable.data('shift');

        event.preventDefault();
        event.stopPropagation();

        if (gds_object.fetching_suggestions) {
            return;
        }

        gds_object.fetching_suggestions = true;

        $.ajax({
            url: gds_object.public_uri + 'gds/shift-suggestions/' + shift_id,
            type: 'GET',
            success: function(data) {
                gds_object.createSuggestionTable(data, popuptable);

            },
            error: function() {
                alert('Kunne ikke finde forslag');
            },
            complete: function() {
                gds_object.fetching_suggestions = false;

            }
        });
    },

    createSuggestionTable: function(data, popuptable) {
        var wrapper = $('<div class="gds-tablewrapper suggestions"><table cellspacing="0" cellpadding="0" border="0" class="gds-suggestion-table"><caption>Forslag</caption><thead><tr><th colspan="2">Deltager</th><th>Mobil</th><th>Fordi ...</th><th>Kontaktet</th><th>Tjanser</th></tr></thead><tbody></tbody></table></div>'),
            tbody   = wrapper.find('tbody'),
            index   = 0,
            append  = '',
            hidden  = '';

        jQuery.each(data, function() {
            append += '<tr class="' + hidden + (index % 2 ? ' gds-altern' : '') + '" data-participant="' + this.id + '"><td><i class="icon-ok assign-to-shift"/></td><td>' + this.name + '</td><td>' + this.mobiltlf + '</td><td>' + this.cause + '</td><td><span>' + this.contacted_about_diy + '</span> <i class="icon-plus-sign mark-contacted"/></td><td>' + this.shifts_assigned + '</td></tr>';

            index++;

            if (index == 15) {
                hidden = 'hidden';
                append += '<tr class="show-rest"><td colspan="6">...</td></tr>';
            }
        });

        tbody.append(append);

        if (popuptable.find('div.suggestions').length) {
            popuptable.find('div.suggestions').remove();
        }

        popuptable.css({width: '1100px'}).children('div.clearit').before(wrapper);

    },

    handleNoshowEvent: function(event) {
        var self  = $(this),
            state = self.is(':checked');

        event.stopPropagation();
        event.preventDefault();

        self.attr('disabled', true);

        $.ajax({
            url: gds_object.public_uri + 'gds/ajax-mark-noshow',
            type: 'POST',
            data: {shift_id: self.data('shift'), participant_id: self.data('participant'), state: state ? 1 : 0},
            success: function(data) {
                self.prop('checked', state);
            },
            complete: function() {
                self.attr('disabled', false);
            }
        });
    },

    makeParticipantHoverHandler: function () {
        var displayPopupTimeout = null,
            $popup = null,
            template = document.getElementById('gds-participant-popup-template').textContent,
            removePopup = function () {
                $popup.remove();
                $popup = null;
            },
            makeElement = function ($row) {
                return ['id', 'name', 'note', 'medical-note', 'phone', 'email', 'age', 'gamemaster', 'assigned-shifts'].reduce(function (agg, next) {
                    return agg.replace('{{' + next + '}}', $row.attr('data-' + next));
                }, template).replace(/\n/, '');
            },
            makeParticipantPopup = function ($row, x, y) {
                var popupHeight = 0,
                    top = y - 30;

                $popup = $(makeElement($row));

                $popup.attr('data-id', $row.attr('data-id'));

                $popup.css({
                    top: 0,
                    left: 0,
                    'z-index': -1
                });

                $popup.appendTo('body');
                popupHeight = $popup.height();

                if (popupHeight + top > document.body.scrollHeight) {
                    top = document.body.scrollHeight - popupHeight - 20;
                }

                $popup.css({
                    top: top + 'px',
                    left: (x + 10) + 'px',
                    'z-index': 1
                });
            };

        return function (e) {
            var $element = $(document.elementFromPoint(e.clientX, e.clientY)),
                $row = $element.closest('.gds-signup-row');

            if (displayPopupTimeout) {
                clearTimeout(displayPopupTimeout);
                displayPopupTimeout = null;

            }

            if ($row.length) {
                if ($popup) {
                    if ($popup.attr('data-id') === $row.attr('data-id')) {
                        return;
                    }

                    removePopup();
                }

                displayPopupTimeout = setTimeout(makeParticipantPopup.bind(null, $row, e.pageX, e.pageY), 300);

                return;
            }

            if ($popup) {
                removePopup();
            }
        };
    },

    debounce: function (callback, time) {
        var waitPeriod = null;

        return function (e) {
            if (waitPeriod) {
                return;
            }

            waitPeriod = setTimeout(function () {
                waitPeriod = null;
            }, time);

            return callback(e);
        };
    },

    setup: function() {
        var that = this;
        // setup event handlers for all links that need it

        $('p#gds-categories').on(
            'click', 'a.gds-category-link', this.handleCategoryLinkClick
        );

        $('#gds-calendar-table').on('click', 'a.gds-shift-link', this.handleShiftLinkClick)
            .on('click', 'a.gds-close-popup', function(e) {
                e.preventDefault();
                that.normaliseShiftLinks();
                $('span.gds-shift-popupdetails').hide();
            })
            .on('mousemove', '.gds-popuptable', this.debounce(this.makeParticipantHoverHandler(), 100))
            .on('click', 'input.no-show', this.handleNoshowEvent)
            .on('click', 'a.gds-remove-participants', this.handleRemoveParticipants)
            .on('click', 'a.gds-add-signups', this.handleAddSignups)
            .on('click', 'a.gds-add-randoms', this.handleAddSignups)
            .on('click', 'a.find-suggestions', this.handleFindSuggestionsEvent)
            .on('click', 'i.assign-to-shift', this.handleAssignToShiftEvent)
            .on('click', 'i.mark-contacted', this.handleMarkContactedEvent)
            .on('click', 'input.gds-search-button', this.handleAjaxSearch)
            .on('click', 'tr.show-rest', this.handleShowRemainingSuggestionsEvent)
            .on('click', 'img[data-role="shift-search"]', this.handleSearchShiftParticipants)
            .on('keydown', 'input.gds-search-box', function(e) {
                if (e.keyCode && e.keyCode == 13) {
                    that.handleAjaxSearch.call(this, e);
                }
            })
            .on('change', 'input.gds-override', this.flipInactiveRows);

    }

};

$(function() {
    var table = $('table.data-table');

    if (table.length) {
        table.dataTable({
            bJQueryUI: true,
            bProcessing: true,
            bLengthChange: true,
            iDisplayStart: 0,
            iDisplayLength: 25
        });
    }
});
