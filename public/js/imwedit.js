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
 * @package   Javascript
 * @author    Peter Lind <peter.e.lind@gmail.com>
 * @copyright 2009-2012 Peter Lind
 * @license   http://www.gnu.org/licenses/gpl.html GPL 3
 * @link      http://www.github.com/Fake51/Infosys
 */

var madwearedit = {

    wear_array: [],
    deltager_id: false,
    public_uri: '',

    deleteRow: function (e){
        var e = e || window.event;
        var elem = e.currentTarget || e.srcElement;
        $(elem.parentNode.parentNode).remove();
    },

    setupIndgang: function(){
        var that = this;
        $('#indgang_tilfoej').click(function(e){
            var select  = $('#indgangselect')[0],
                opt_top = select.options[select.selectedIndex];

            if (opt_top.value > 0 && !that.checkForAlreadySelected('indgang[]', '#indgang_edit', opt_top.value)) {
                $('<tr><td class="choice"><input type="hidden" name="indgang[]" value="' + opt_top.value + '">' + opt_top.text + '</td><td><input type="button" value="Slet"></td></tr>').appendTo($('#indgang_edit tbody'));
            }
        });

        $('#indgang_edit').on('click', 'input', function() {
            $(this).closest('tr').remove();
        });
    },

    setupMad: function(){
        var that = this,
            select_bottom = $('#madselect_bottom')[0],
            select_top    = $('#madselect_top')[0];

        $('#mad_tilfoej').click(function(e){
            if (select_bottom.selectedIndex < 0) {
                return;
            }

            var opt_top    = select_top.options[select_top.selectedIndex],
                opt_bottom = select_bottom.options[select_bottom.selectedIndex];

            if (opt_top.value > 0 && opt_bottom.value > 0 && !that.checkForAlreadySelected('madtider[]', '#mad_edit', opt_bottom.value)) {
                $('<tr><td class="choice"><input type="hidden" name="madtider[]" value="' + opt_bottom.value + '">' + opt_top.text + ' ' + opt_bottom.text + '</td><td><input type="button" value="Slet"></td></tr>').appendTo($('#mad_edit tbody'));
            }
        });

        $('#mad_edit').on('click', 'input', function() {
            $(this).closest('tr').remove();
        });

        $(select_top).change(function(e){
            var id;

            $(select_bottom).html('');

            if (select_top.selectedIndex > 0) {
                id = select_top.options[select_top.selectedIndex].value;

                $.ajax({
                    url: that.public_uri + 'mad/ajaxgetmadtider/' + id,
                    type: 'get',
                    success: function(data){
                        that.ajaxToSelect($(select_bottom), data);
                    }
                });
            }
        });
    },

    setupWear: function(){
        var that = this,
            select_top    = $('#wearselect_top')[0],
            select_middle = $('#wearselect_middle')[0],
            select_bottom = $('#wearselect_bottom')[0];

        $('#wear_tilfoej').click(function(e) {
            var opt_top,
                opt_middle,
                opt_bottom,
                inp_antal;

            if (select_middle.selectedIndex < 0) {
                return;
            }

            opt_top    = select_top.options[select_top.selectedIndex];
            opt_middle = select_middle.options[select_middle.selectedIndex];
            opt_bottom = select_bottom.options[select_bottom.selectedIndex];
            inp_antal  = $('#wear_antal').val();

            if (opt_top.value && opt_bottom.value && opt_middle.value  && inp_antal > 0 && !that.checkForAlreadySelectedWear(opt_middle.value, opt_bottom.value)) {
                $('<tr><td class="choice"><input type="hidden" name="wearpriser[]" value="' + opt_middle.value + '"><input type="hidden" name="wearantal[]" value="' + inp_antal + '"><input type="hidden" name="wearsize[]" value="' + opt_bottom.value + '">' + inp_antal + ' stk. ' + opt_top.text + ' str. ' + opt_bottom.text + ' - (' + opt_middle.text + ')</td><td><input type="button" value="Slet"></td></tr>').appendTo($('#wear_edit tbody'));
            }

        });

        $('#wear_edit').on('click', 'input', function() {
            $(this).closest('tr').remove();
        });

        $(select_top).change(function(e){
            var id,
                response,
                theArray,
                to_die;

            $(select_middle).html('');
            $(select_bottom).html('');

            if (select_top.selectedIndex > 0) {
                id = select_top.options[select_top.selectedIndex].value;
                $.ajax({
                    url: that.public_uri + 'wear/ajaxgetwear/' + id,
                    type: 'get',
                    success: function(data){
                        that.ajaxToSelect($(select_middle), data);

                        response = $.parseJSON(data).pairs;
                        theArray = that.prepareSecondWearArray(response[0].sizerange);
                        for (var i = 0; i < theArray.length; i++) {
                            $(select_bottom).append('<option value="' + theArray[i].value + '">' + theArray[i].text + '</option>');
                        }
                    }
                });
            }
        });
    },

    setupGDS: function() {
        var that     = this,
            gds_add  = $('#gds_tilfoej'),
            gds_time = $('#gds_tid'),
            gds_name = $('#gds_navn');

        gds_add.click(function(e) {
            if (gds_time.prop('selectedIndex') < 0) {
                return;
            }

            if (gds_name.val() && gds_time.val() && !that.checkForAlreadySelected('gdsvagter[]', '#gds_edit', gds_time.val())) {
                $('<tr><td class="choice"><input type="hidden" name="gdsvagter[]" value="' + gds_time.val() + '"/><input type="hidden" name="gds[]" value="' + gds_name.val() + '"/>' + gds_name.find('option:selected').text() + ' ' + gds_time.find('option:selected').text() + '</td><td><input type="button" value="Slet" class="delete"/></td></tr>').appendTo('#gds_edit tbody');
            }
        });

        $('#gds_edit').on('click', 'input.delete', function(e) {
            $(this).closest('tr').remove();
        });

        gds_name.change(function(e) {
            gds_time.html('');
            if (gds_name.prop('selectedIndex') > 0) {
                $.ajax({
                    url: that.public_uri + 'gds/ajaxvagttider/' + that.deltager_id + '/' + gds_name.val(),
                    type: 'GET',
                    success: function(transport) {
                        that.ajaxToSelect(gds_time, transport);
                    }
                });
            }
        });

    },

    setupGDSShifts: function() {
        var that     = this,
            gds_add  = $('#gds_tilfoej'),
            gds_time = $('#gds_tid'),
            gds_name = $('#gds_navn');

        gds_add.click(function(e) {
            if (gds_time.prop('selectedIndex') < 0) {
                return;
            }

            if (gds_name.val() && gds_time.val() && !that.checkForAlreadySelected('gdsvagter[]', '#gds_edit', gds_time.val())) {
                $('<tr><td class="choice"><input type="hidden" name="period[]" value="' + gds_time.val() + '"/><input type="hidden" name="gds[]" value="' + gds_name.val() + '"/>' + gds_name.find('option:selected').text() + ' ' + gds_time.find('option:selected').text() + '</td><td><input type="button" value="Slet" class="delete"/></td></tr>').appendTo('#gds_edit tbody');
            }
        });

        $('#gds_edit').on('click', 'input.delete', function(e) {
            $(this).closest('tr').remove();
        });

        gds_name.change(function(e) {
            gds_time.html('');
            if (gds_name.prop('selectedIndex') > 0) {
                $.ajax({
                    url: that.public_uri + 'gds/ajaxshiftperiods/' + that.deltager_id + '/' + gds_name.val(),
                    type: 'GET',
                    success: function(transport) {
                        that.ajaxToSelect(gds_time, transport);
                    }
                });
            }
        });

    },

    setupAktivitet: function(){
        var that = this,
            select_top    = $('#aktivitet_navn')[0],
            select_middle = $('#aktivitet_tid')[0],
            select_bottom = $('#aktivitet_hold')[0],
            select_last   = $('#aktivitet_sl')[0];

        $('#aktivitet_tilfoej').click(function(e) {
            var opt_top    = select_top.options[select_top.selectedIndex],
                opt_middle = select_middle.options[select_middle.selectedIndex],
                opt_bottom = select_bottom.options[select_bottom.selectedIndex],
                opt_last   = select_last.options[select_last.selectedIndex];

            if (select_bottom.selectedIndex < 0) {
                return;
            }

            if (opt_top.value && opt_bottom.value && opt_middle.value && opt_last.value && !that.checkForAlreadySelected('hold_id[]','#aktivitet_edit', opt_bottom.value)) {
                $('<tr><td class="choice"><input type="hidden" name="hold_id[]" value="' + opt_bottom.value + '"><input type="hidden" name="type[]" value="' + opt_last.value + '">' + opt_top.text + ' &mdash; ' + opt_middle.text + ', ' + opt_last.text + '</td><td><input type="button" value="Slet"></td></tr>').appendTo('#aktivitet_edit tbody');

            }

        });

        $('#aktivitet_edit').on('click', 'input', function() {
            $(this).closest('tr').remove();
        });

        $(select_top).change(function(e){
            $(select_middle).html('');
            $(select_bottom).html('');
            $(select_last).html('');

            if (select_top.selectedIndex > 0) {
                var id = select_top.options[select_top.selectedIndex].value;
                $.ajax({
                    url: that.public_uri + 'aktivitet/getafviklinger/' + that.deltager_id + '/' + id,
                    type: 'GET',
                    success: function(data) {
                        that.ajaxToSelect($(select_middle), data);
                    }
                });
            }

        });

        $(select_middle).change(function(e){
            $(select_bottom).html('');
            $(select_last).html('');

            if (select_middle.selectedIndex > 0) {
                var id = select_middle.options[select_middle.selectedIndex].value;
                $.ajax({
                    url: that.public_uri + 'aktivitet/gethold/' + id,
                    type: 'GET',
                    success: function(data){
                        that.ajaxToSelect($(select_bottom), data);
                    }
                });
            }

        });

        $(select_bottom).change(function(e){
            $(select_last).html('');
            if (select_bottom.selectedIndex > 0) {
                var id = select_bottom.options[select_bottom.selectedIndex].value;
                $.ajax({
                    url: that.public_uri + 'aktivitet/getholdneeds/' + id,
                    type: 'GET',
                    success: function(data){
                        that.ajaxToSelect($(select_last), data);
                    }
                });
            }
        });
    },


    setupTilmeldinger: function(){
        var that = this,
            select_top    = $('#aktivitet_navn')[0],
            select_middle = $('#aktivitet_tid')[0],
            select_bottom = $('#aktivitet_prio')[0],
            select_last   = $('#aktivitet_sl')[0];

        $('#aktivitet_tilfoej').click(function(e){

            if (select_last.selectedIndex < 0) {
                return;
            }

            var opt_top    = select_top.options[select_top.selectedIndex],
                opt_middle = select_middle.options[select_middle.selectedIndex],
                opt_bottom = select_bottom.options[select_bottom.selectedIndex],
                opt_last   = select_last.options[select_last.selectedIndex],
                prio,
                prio_text;

            if (opt_top.value && opt_middle.value && opt_last.value && !that.checkForAlreadySelected('afvikling_id[]','aktivitet_edit',opt_middle.value)) {
                if (opt_last.value == 'spilleder') {
                    prio      = 1;
                    prio_text = ''
                } else {
                    prio      = opt_bottom.value;
                    prio_text = ', ' + prio + '. prioritet';
                }

                $('<tr><td class="choice"><input type="hidden" name="afvikling_id[]" value="' + opt_middle.value + '"/><input type="hidden" name="type[]" value="' + opt_last.value + '"/><input type="hidden" name="prioritet[]" value="' + prio + '"/>' + opt_top.text + ' - ' + opt_last.text + prio_text + ', ' + opt_middle.text + '</td><td><input type="button" value="Slet"/></td></tr>').appendTo('#aktivitet_edit tbody');

            }

        });

        $('#aktivitet_edit').on('click', 'input[type=button]', function() {
            $(this).closest('tr').remove();
        });

        $(select_top).change(function(e){
            var id,
                contents = '';

            $(select_bottom).hide();
            $(select_last).hide();
            $(select_middle).html('');

            if (select_top.selectedIndex > 0) {
                id = select_top.options[select_top.selectedIndex].value;
                $.ajax({
                    url: that.public_uri + 'aktivitet/getafviklinger/0/' + id,
                    type: 'GET',
                    success: function(data){
                        that.ajaxToSelect($(select_middle), data);
                    }
                });
            }
        });

        $(select_middle).change(function(e) {
            if (select_middle.selectedIndex > 0) {
                select_last.selectedIndex = 0;
                $(select_last).show();
            } else {
                $(select_last).hide();
            }

            $(select_bottom).hide();

        });

        $(select_last).change(function(e) {
            if (select_last.selectedIndex > 0 && select_last.options[select_last.selectedIndex].value == 'spiller') {
                $(select_bottom).show();
            } else {
                $(select_bottom).hide();
            }
        });
    },

    setupHold: function() {
        var that = this,
            activity_name = $('#aktivitet_navn'),
            activity_time = $('#aktivitet_tid'),
            activity_room = $('#lokale');

        activity_name.change(function(e) {
            activity_room.html('');
            activity_time.html('');
            if (activity_name.prop('selectedIndex') > 0) {
                $.ajax({
                    url: that.public_uri + 'aktivitet/getafviklinger/0/' + activity_name.val(),
                    type: 'GET',
                    success: function(transport) {
                        that.ajaxToSelect(activity_time, transport);
                    }
                });
            }
        });

        activity_time.change(function(e) {
            activity_room.html('');
            if (activity_time.prop('selectedIndex') > 0) {
                $.ajax({
                    url: that.public_uri + 'lokaler/getlokaler/' + activity_time.val(),
                    type: 'GET',
                    success: function(transport) {
                        that.ajaxToSelect(activity_room, transport);
                    }
                });
            }
        });
    },

    checkForAlreadySelected: function(input_name, table_name, input_value){
        var inputs       = $(table_name + ' tbody tr input'),
            return_value = false;

        inputs.each(function(idx, item) {
            if (item.name == input_name && item.value == input_value) {
                return_value = true;
            }
        });

        return return_value;
    },

    checkForAlreadySelectedWear: function(wearpris, size){
        var rows = $('#wear_edit tr'),
            return_value = false;

        rows.each(function(idx, item) {
            var inputs = $(item).find('input'),
                wp = false,
                s  = false;

            inputs.each(function(idx_inner, item_inner) {
                if (item_inner.name == 'wearpriser[]' && item_inner.value == wearpris) {
                    wp = true;
                }

                if (item_inner.name == 'wearsize[]' && item_inner.value == size) {
                    s = true;
                }
            });

            if (wp && s) {
                return_value = true;
            }
        });

        return return_value;
    },

    prepareSecondWearArray: function(sizerange){
        var sizeparts = sizerange.split('-'),
            start     = this.wearSizes.indexOf(sizeparts[0]),
            end       = this.wearSizes.indexOf(sizeparts[1]);

        if (start === -1 || end === -1) {
            return [];
        }

        return this.wearSizes.slice(start, end)
            .map(function (item) {
                return {
                    value: item,
                    text: item
                };
            });
    },

    blankSelect: function(select_id){
        var opts = $(select_id).getElementsByTagName('option');
        while (opts[0]) 
        {
            $(opts[0]).remove();
        }
    },

    ajaxToSelect: function(element, data){
        var received = $.parseJSON(data).pairs;

        for (var i = 0; i < received.length; i++) {
            element.append($('<option value="' + received[i].value + '"' + (received[i].disabled == 'true' ? ' disabled="disabled"' : '') + '>' + received[i].text + '</option>'));
        }
    }
}

function addTilmeldtAktivitet(afvikling_id, type, inp_obj){
    var ajax = new Ajax.Request(that.public_uri + 'aktivitet/getrandomhold/' + afvikling_id +'/' + type, {
        method: 'get',
        onSuccess: function(transport){
            if (transport.responseText == '')
            {
                alert('Der er ingen ledige hold for den afvikling af aktiviteten.');
            }
            else
            {
                var tab = $('aktivitet_edit');
                var firsttd = document.createElement('td');
                var secondtd = document.createElement('td');
                firsttd.setAttribute('class', 'choice');
                firsttd.innerHTML = transport.responseText;
                if (madwearedit.checkForAlreadySelected('hold_id[]', 'aktivitet_edit', firsttd.firstChild.value))
                {
                    $(firsttd).remove();
                    $(secondtd).remove();
                }
                else
                {
                    secondtd.innerHTML = "<input type='button' value='Slet'/>";
                    var sletinput = secondtd.childElements();
                    $(sletinput[0]).observe('click',function(e){
                       that.deleteRow(e); 
                    });
                    var newrow = document.createElement('tr');
                    newrow.appendChild(firsttd);
                    newrow.appendChild(secondtd);
                    var tablebody = $('aktivitet_edit').childElements();
                    tablebody[0].appendChild(newrow);
                    $(inp_obj).parentNode.remove();
                }
            }
        }
    });
}
