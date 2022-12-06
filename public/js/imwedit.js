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
            select_item     = $('#wear-select-item'),
            select_price    = $('#wear-select-price'),
            attribute_div   = $('#wear-select-attributes');

        $('#wear_tilfoej').click(function(e) {
            if (select_price.val() == '') {
                return;
            }

            let item_id     = select_item.val();
            let item_text   = select_item.find('option:selected').text();
            
            let price_id    = select_price.val();
            let price_text  = select_price.find('option:selected').text();

            let attributes = {};
            attribute_div.find('select').each(function () {
                let type = $(this).attr('attribute-type');
                attributes[type] = $(this).val();
                // Add text for attributes except size that we handle differently
                if (type === 'size') {
                    item_text += " str. " + $(this).find('option:selected').text();
                } else {
                    item_text += "-" + $(this).find('option:selected').text();
                }
            })

            let amount   = $('#wear_antal').val();

            // Check for already added wear with same attributes
            let item = that.checkForAlreadySelectedWear({
                price: price_id,
                ...attributes,
            })

            if(item) {
                let amount_input = $(item).find('input[field=amount]');
                let new_amount = parseInt(amount_input.val()) + parseInt(amount);
                amount_input.val(new_amount);
                $(item).find('span.wear-amount').text(new_amount);
                return;
            }

            // Create a new row for the item
            if (item_id && price_id && amount > 0) {
                let last_index = 0;
                $('#wear_edit tr').each(function() {
                    last_index = Math.max(parseInt($(this).attr('index')), last_index);
                })
                let index = last_index + 1;

                let fields = 
                `<input type="hidden" name="wear[${index}][price]" field="price" value="${price_id}">
                <input type="hidden" name="wear[${index}][amount]" field="amount" value="${amount}">`;

                for (const [type, value] of Object.entries(attributes)) {
                    fields += `<input type="hidden" name="wear[${index}][attribute][${type}]" field="${type}" value="${value}">`;
                }

                $('#wear_edit tbody').append(`
                    <tr index="${index}">
                        <td class="choice">
                            ${fields}
                            <span class="wear-amount">${amount}</span> stk. ${item_text} - ( ${price_text} )
                        </td>
                        <td>
                            <input type="button" value="Slet">
                        </td>
                    </tr>`);
            }

        });

        $('#wear_edit').on('click', 'input', function() {
            $(this).closest('tr').remove();
        });

        let arttibute_select_update = function(select) {
            let option = $(select.selectedOptions[0]);
            let variants = option.attr('variants').split(',');
        
            // Filter for common variants among previous options
            let jq_select = $(select);
            for (let prev = jq_select.prev(); prev.length > 0; prev = prev.prev()) {
                let pre_option = $(prev[0].selectedOptions[0]);
                variants = variants.filter(function(value) {
                    return pre_option.attr('variants').split(',').includes(value);
                })
            }
        
            // Filter out options not available for current variants
            for (let next = jq_select.next(); next.length > 0; next = next.next()) {
                let next_select = next[0];
                let first_valid;
                let invalid_selection = false;
                for(const option of next_select.options) {
                    let common_variants = variants.filter(function(value) {
                        return $(option).attr('variants').split(',').includes(value);
                    })
                    if (common_variants.length > 0) {
                        first_valid = first_valid ?? option;
                        option.disabled = false;
                        option.hidden = false;
                    } else {
                        // The option is invalid
                        if (option == next_select.selectedOptions[0]) {
                            // The option is the one curently selected, so we have to change it
                            invalid_selection = true;
                        }
                        option.disabled = true;
                        option.hidden = true;
                    }
                }
                if (invalid_selection) next_select.value = first_valid.value;
            }
        }
        
        select_item.change(function(){
            select_price.empty();
            attribute_div.empty();

            if (select_item.val() !== '') {
                let id = select_item.val();
                let url = that.public_uri + 'wear/ajaxgetwear/' + id
                $.getJSON(
                    url,
                    "",
                    function(data){
                        // There should be some available prices for the wear item
                        if (!(data.prices instanceof Object)) {
                            alert("Der skete en fejl ved hentning a wear priser");
                            return;
                        }
                        for (const [index, price] of Object.entries(data.prices)) {
                            select_price.append(`<option value="${price.id}">${price.text}</option>`);
                        }

                        // The wear item may not have any variants
                        if (data.variants.length == 0) return;

                        for (const variant_id in data.variants) {
                            const variant = data.variants[variant_id];
                            for (const [type, attributes] of Object.entries(variant)) {
                                // Find the select element
                                let select_attribute = attribute_div.find(`select#wear-attribute-${type}`);
                                // Add a select element if it doesn't exist
                                if (select_attribute.length == 0) {
                                    select_attribute = $(`<select id="wear-attribute-${type}" attribute-type="${type}"></select>`);
                                    attribute_div.append(select_attribute);
                                }
                                select_attribute.change(function(evt) {
                                    arttibute_select_update(evt.delegateTarget);
                                });

                                for (const [id, att] of Object.entries(attributes)) {
                                    let option = select_attribute.find(`option[value=${id}]`);
                                    if (option.length > 0) {
                                        // If option exist, add current variant to it
                                        option.attr('variants', option.attr('variants') + `,${variant_id}`)
                                    } else {
                                        // Else create the option
                                        select_attribute.append(`<option value="${id}" variants="${variant_id}" >${att.desc_da}</option>`);
                                    }
                                }
                            }
                        }

                        // Update available options
                        arttibute_select_update(attribute_div.find('select')[0]);
                    },
                ).fail(function(jqXHR) {
                    alert("Der skete en fejl ved hentning a information om wear");
                    console.error("There was an error requesting information from ", url, " Data:", jqXHR);
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

    checkForAlreadySelectedWear: function(attributes){
        var rows = $('#wear_edit tr');
        let same_item;

        rows.each(function(idx, item) {
            var inputs = $(item).find('input'),
                diff  = false;

            // Check for any diferences with current row 
            inputs.each(function() {
                let input = $(this);
                for( const [type, value] of Object.entries(attributes)) {
                    if (input.attr('field') == type && input.val() != value) {
                        diff = true;
                        return false; // Stop the loop
                    }
                }
            });

            // We found a match
            if (!diff) {
                same_item = item;
                return false; // Stop the loop
            } 
        });

        return same_item;
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
