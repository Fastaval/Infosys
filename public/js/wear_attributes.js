"using strict";

jQuery(function() {
  WearAttributesControl.init();
});


class WearAttributesControl {
  static init() {
    this.init_add_item_buttons();


    // Init add category button
    jQuery('button#add-category').click(function(evt) {
      WearAttributesControl.add_category(evt);
    });

    jQuery('td[contenteditable="true"]').on('input', function(evt){
      WearAttributesControl.desciption_change(evt);
    });
  }

  static desciption_change(evt) {
    let cell = jQuery(evt.delegateTarget);
    let row = cell.closest('tr');
    let button = row.find('button');
    if (button.length == 0) {
      button = jQuery('<button class="save">Gem</button>');
      row.find('.button-cell').append(button);

      // Init save button logic
      button.click(function (evt) {
        WearAttributesControl.save(evt);
      });
    } else {
      button.show();
    }
  }

  static add_category(evt) {
      // Insert table for new attribute type
      let wrapper = jQuery('<div class="category-wrapper"></div>');
      wrapper.append(`
        <h2 contentEditable="true">new_category</h2>
        <table id="new_category">
          <thead>
            <tr><th>Dansk</th><th>Engelsk</th></tr>
          </thead>
          <tbody>
            <tr><td><button class="add-item">Tilføj egenskab</button></td></tr>
          </tbody>
        </table>`
      );
      jQuery('div#wear-attribute-categories').append(wrapper);

      // Update table id on name change
      wrapper.find('h2').on('input', function (evt) {
        let header = jQuery(evt.delegateTarget);
        let table = header.next();
        table.attr('id', header.text());
      })

      // Init button functionality in new section
      this.init_add_item_buttons(wrapper);
  }

  static init_add_item_buttons(element) {
    // Set element default if not set
    element = element ?? jQuery(':root');

    // Init buttons inside element
    element.find('button.add-item').click(function(evt) {
      let button = jQuery(evt.delegateTarget);
      let button_row = button.closest('tr');
      let attribute_row = jQuery('<tr></tr>');
      attribute_row.append('<td contentEditable="true">navn</td><td  contentEditable="true">name</td><td class="button-cell"><button class="save">Gem</button></td>');
      attribute_row.insertBefore(button_row);

      // Init save button logic
      attribute_row.find('button.save').click(function (evt) {
        WearAttributesControl.save(evt);
      });

      attribute_row.find('td[contenteditable="true"]').on('input', function(evt){
        WearAttributesControl.desciption_change(evt);
      });  
    });
  }

  static save(evt) {
    let button = jQuery(evt.delegateTarget);
    let row = button.closest('tr');
    let id = row.attr('id');
    let table = row.closest('table');
    let category = table.attr('id');
    let td_da = row.find('td').first();
    let td_en = td_da.next();

    jQuery.ajax({
      type: 'POST',
      dataType: 'json',
      url: '/wear/attributes',
      data: {
        id: id,
        type: category,
        en: td_en.text(),
        da: td_da.text(),
      },
      success: function(result) {
        button.hide();
      }
    })
  }
}