    /**
     * Copyright (C) 2009  Peter Lind
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
     * @copyright 2009 Peter Lind
     * @license   http://www.gnu.org/licenses/gpl.html GPL 3
     * @link      http://www.github.com/Fake51/Infosys
     */

var wear_object = {
    setup: function(){
        var that = this;

        this.select = $('#category-select');

        $('#wear-priser').on('click', 'input.remove-wearprice', function() {
            that.removePrice($(this));
        }).on('click', '#add-wearprice', function() {
            that.addPrice($(this));
        });

        $('#add-variant').click(function() {
            that.addVariant();
        });

        $('#attributes input').click(function(evt) {
            that.attributeClick(evt.delegateTarget);
        })

        $('#add-image').click(function() {
            $('#add-image-dialog-modal').removeClass('closed');
        });

        $('.image-selection-wrapper li').click(function(evt) {
            that.imageSelect(evt.delegateTarget);
        });

        $('#add-image-confirm').click(function() {
            that.imageConfirm(true);
        });

        $('#add-image-cancel').click(function() {
            that.imageConfirm(false);
        });

        $('#image-attribute-list input').click(function(evt) {
            that.imageAttributeClick(evt.delegateTarget);
        });

        $('.folding-section').each(function() {
            let section = $(this);
            section.find('.folding-element').hide();
            section.find('.folding-button').click(function() {
                that.foldSection(section);
            });
        })

        this.sortCategories();
    },

    sortCategories: function() {
        this.select.find('option').detach().sort(function(a, b) {
            return a.text < b.text ? -1 : 1;
        }).appendTo(this.select);
    },

    removePrice: function(self) {
        var select = $('#category-select'),
            row    = self.closest('tr'),
            price  = row.find('input[name="wearprice_price[]"]'),
            name   = row.find('td').eq(0);

        select.append('<option value="' + price.val() + '">' + name.text() + '</option>');
        row.remove();
        this.sortCategories();
    },

    addPrice: function(button){
        var add_row  = button.closest('tr'),
            select   = $('#category-select'),
            selected = select.find('option:selected'),
            price    = $('#category-price'),
            table    = add_row.closest('table');

        if (select.val()) {
            table.find('tbody').append('<tr><td>' + selected.text() + '</td><td><input name="wearpriceid[]" type="hidden" value="0"/><input name="wearprice_category[]" type="hidden" value="' + select.val() + '"/><input name="wearprice_price[]" type="text" value="' + price.val() + '"/><input type="hidden" name="wearprice_wearid[]" value="' + $('#wear-id').val() + '"/></td><td><input type="button" class="remove-wearprice" value="Slet"/></td></tr>');

            selected.remove();

            price.val('');
        }
    },

    addVariant: function() {
        let variant_count = $('#attributes .attribute-variant').length;
        let varaint_div = $('<div class="attribute-variant">');
        for(const type in this.attributes) {
            let type_div = $('<div class="attribute-type-list">');
            type_div.append('<h3>'+type+'</h3>');
            let entries = Object.entries(this.attributes[type]).sort(function(a,b){
                return a[1].position - b[1].position;
            });
            for(const [id, att] of entries) {
                type_div.append(`
                    <div class="attribute-input-wrapper">
                        <input type="checkbox" id="attribute-${variant_count}-${id}" name="attributes[${variant_count}][]" value="${id}">
                        <label for="attribute-${variant_count}-${id}">${att.desc_da}</label>
                    </div>
                `);
            }
            varaint_div.append(type_div);
        }
        $('#attributes').append(varaint_div);
        
        // Add onclick to new checkboxes
        let that = this;
        varaint_div.find('input').click(function(evt) {
            that.attributeClick(evt.delegateTarget);
        })
    },

    attributeClick(input) {
        let checkbox = $(input);
        let attribute_id = checkbox.val();
        let type = checkbox.closest('.attribute-type-list').find('h3').text();

        // Add attribute to image selection if needed
        if (checkbox.prop('checked')) {
            if($(`input#attribute-img-${attribute_id}`).length == 1) return; // Checkbox already exist

            let image_type_headers = $('#image-attribute-list .attribute-type-list h3');
            let type_section;
            let section_header;
            image_type_headers.each(function() {
                if ($(this).text() === type) {
                    section_header = $(this);
                }
            });

            // The attribute section doesn't exist for the given type
            if (!section_header) {
                // create type section
                type_section = $('<div class="attribute-type-list"></div>');
                $('#image-attribute-list').append(type_section);
                
                section_header = $(`<h3>${type}</h3>`)
                type_section.append(section_header);
            } else {
                type_section = $(section_header).closest('.attribute-type-list');
            }

            // Create the input wrapper and input
            let input_wrapper = $('<div class="attribute-input-wrapper"></div>');
            type_section.append(input_wrapper);

            let text = $(`label[for="${checkbox.attr('id')}"]`).text();
            input_wrapper.append(`
                <input type="checkbox" id="attribute-img-${attribute_id}" attribute-id="${attribute_id}">
                <label for="attribute-img-${attribute_id}">${text}</label>
            `);

            // Add click functionality to new input
            let that = this;
            input_wrapper.find('input').click(function(evt) {
                that.imageAttributeClick(evt.delegateTarget);
            });
        }
    },

    imageSelect(image_name) {
        let li = $(image_name);
        let wrapper = li.closest('.image-selection-wrapper');
        wrapper.find('li').removeClass('selected');
        li.addClass('selected');
        let preview = wrapper.find('.image-preview');
        preview.find('img').attr('src', li.attr('file'));
        preview.attr('selected-image', li.attr('image-id'));

        let input_wrapper = wrapper.find('#image-attribute-inputs');
        if (input_wrapper.length == 1) {
            $('#image-attribute-list input').prop('checked', false);
            input_wrapper.find('input').each(function() {
                let input = $(this);
                let attribute_id = input.val();
                if (input.attr('image-id') !== li.attr('image-id')) return;

                $('#image-attribute-list #attribute-img-'+attribute_id).prop('checked', true);
            })
        }
    },

    imageConfirm(confirm) {
        // Close dialog
        $('#add-image-dialog-modal').addClass('closed');
        
        // Get image id
        let image_id = $('#dialog-image-preview').attr('selected-image');
        
        // Reset preview
        $('#dialog-image-preview').find('img').attr('src', '');
        $('#dialog-image-preview').attr('selected-image', '');
        
        if (!image_id && confirm) return;

        image_item = $(`#add-image-dialog li[image-id=${image_id}]`)
        image_item.removeClass('selected');
        $('#image-list-wrapper ul').append(image_item);
    },

    imageAttributeClick(input) {
        let checkbox = $(input);
        let attribute_id = checkbox.attr('attribute-id');

        image_id = $('#image-list-wrapper .image-preview').attr('selected-image');
        if(!image_id) return; // No image selected

        if (checkbox.prop('checked')) {
            $('#image-attribute-inputs').append(
                `<input type="hidden" name="wear_image[${image_id}][]" id="wear-image-${image_id}-attribute-${attribute_id}" image-id="${image_id}" value="${attribute_id}">`
            )
        } else {
            $('#image-attribute-inputs').find(`#wear-image-${image_id}-attribute-${attribute_id}`).remove();
        }
    },

    foldSection(section) {
        let button = section.find('.folding-button')
        if(button.hasClass('open')) {
            button.removeClass('open').addClass('closed');
            section.find('.folding-element').hide();
        } else {
            button.removeClass('closed').addClass('open');
            section.find('.folding-element').show();
        }
    }
};

wear_object.setup();
