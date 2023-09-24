"using strict";

jQuery(function() {
  SignupPageControls.init();
});

class SignupPageControls {
  static selection_string = "fieldset, .selectable";
  
  static current_selection = null;
  static current_hover = null;
  static current_page = null;

  static context_menu_items = {
    add$section: {
      text: "Tilføj Sektion",
      need: 'page',
    },
    add$paragraph: {
      text: "Tilføj Tekstelement",
      need: 'section',
    },
    add$text_input: {
      text: "Tilføj Tekst input",
      need: 'section',
    },
    add$text_area: {
      text: "Tilføj Stort Tekstfelt",
      need: 'section',
    },
    add$checkbox: {
      text: "Tilføj Checkboks",
      need: 'section',
    },
    add$date: {
      text: "Tilføj Dato",
      need: 'section',
    },
    add$telephone: {
      text: "Tilføj Telefonnummer",
      need: 'section',
    },
    add$email: {
      text: "Tilføj Email",
      need: 'section',
    },
    add$radio: {
      text: "Tilføj Radioknapper",
      need: 'section',
    },
    add$radio_option: {
      text: "Tilføj Valgmulighed",
      need: [
        'item:radio',
        'tiem:select',
      ],
    },
    add$list: {
      text: "Tilføj liste",
      need: 'section',
    },
  }
  
  static init() {
    SignupAdminControls.save_function = function(element) {
      SignupPageControls.text_submit(element);
    }

    jQuery(window).on('popstate', function(evt) {
      SignupPageControls.nav_click(evt.originalEvent.state.page);
    });

    //---------------------------------------------------------------------------------
    // Context menu
    //---------------------------------------------------------------------------------
    // Create context menu
    let context_menu = jQuery('<div id="contextmenu" class="closed"></div>');
    for(const [id, def] of Object.entries(this.context_menu_items)){
      let menuitem = jQuery('<div class="menu-item"><div>');
      menuitem.text(def.text);
      menuitem.attr('id', id);
      if (Array.isArray(def.need)) {
        menuitem.attr('need', def.need.join(' '));
      } else {
        menuitem.attr('need', def.need);
      }
      context_menu.append(menuitem);
    }
    jQuery('body').append(context_menu);

    // Opening context menu
    jQuery('#signup-admin-container').contextmenu(function(event) {
      if(jQuery(event.target).hasClass('editable')) return true;

      // Filter out disabled actions
      let selection = SignupPageControls.current_selection;
      let selected_id = selection ? selection.closest('fieldset').attr('id') : "";
      let selected_type = selection ? selection.closest('fieldset.signup-page-item').attr('item-type') : "";
      context_menu.find('.menu-item').each(function (){
        let element = jQuery(this)
        let needs = element.attr('need');
        if (needs === undefined) return;
        
        needs = needs.split(' ');
        for (const need of needs) {
          let [item, type] = need.split(":")
          if (!selected_id.includes(item) || (type && type != selected_type)) continue;
            
          element.show();
          return;
        }
      })

      // Set poistion of the menu
      let ypos = Math.min(event.clientY, window.innerHeight - context_menu.height() - 1);
      context_menu.css({
        left: event.clientX,
        top: ypos
      });

      // Show the menu
      context_menu.removeClass('closed').addClass('open');
      return false;
    })

    // Close context menu when clicking anywhere
    jQuery('html').click(function() {
      context_menu.removeClass('open').addClass('closed');
    });

    // Clicking a context menu item
    jQuery('.menu-item').click(function (event){
      let [action, type] = jQuery(event.target).attr('id').split("$", 2);
      if (action == 'add' && type == 'section') {
        SignupPageControls.add_section();
        return;
      }
      if (action == 'add' && type == 'radio_option') {
        SignupPageControls.add_option();
        return;
      }
      if (action == 'add') {
        SignupPageControls.insert_item(type);
      } 
    })
  }

  static pagelist_ready(pages) {
    // Get current page from url
    let match = location.pathname.match(/\/([^/]+)$/);
    let page = match ? match[1] : null;

    if(pages[page]) this.current_page = page;
  }

  static init_element(element) {
    this.initSelectables(element);
    this.initEditables(element);
    this.initSettings(element);
  }

  static nav_click(key) {
    if (key == this.current_page) return;
  
    // Get base url from location
    let url = location.pathname.replace(this.current_page, "");
    if (url.lastIndexOf('/') != url.length -1) url += '/';
  
    // Save new key
    this.current_page = key;

    // Update visuals to selected page
    this.show_page(key);
    
    // Set addressbar
    window.history.pushState({page:key},"", url+key);
  }

  static show_page(key) {
    let nav = SignupAdminRender.nav;
    nav.find('.selected').removeClass('selected');
    nav.find('[page-id='+key+']').addClass('selected');

    jQuery('.signup-page').hide();
    jQuery('.signup-page#page\\:'+key).show();
  }

  // Selection
  static initSelectables(element) {
    // Finde selectables in element (including the element itself)
    let selectables = element.find(this.selection_string)
    if (element.is(this.selection_string)) {
      selectables = selectables.add(element);
    }

    // Select item when clicked
    selectables.click(function(event) {
      SignupPageControls.setSelection(event.target);
    })

    // Highlight selectables when hovered 
    selectables.mouseenter(function(event){
      SignupPageControls.setSelection(event.target, 'hovering', 'current_hover');
    });
    selectables.mouseleave(function(event){
      SignupPageControls.setSelection(null, 'hovering', 'current_hover');
      jQuery(event.relatedTarget).trigger('mouseenter');
    });
  }

  static setSelection(element, css_class = 'selected', storage = 'current_selection') {
    this[storage] && this[storage].removeClass(css_class);
    if (!element) { // We are just unsetting the selection
      this[storage] == null;  
      return;
    }
    this[storage] = jQuery(element).closest(SignupPageControls.selection_string);
    this[storage].addClass(css_class);
    this[storage].context = document;
  }

  // Editables
  static initEditables(element) {
    let editables = element.find('.editable').attr('contentEditable', true);
    editables.each(function() {
      SignupAdminControls.reset_history(jQuery(this));
    });
    editables.on('input', function(event) {
      SignupAdminControls.element_change(event.target);
    });
    editables.keydown(function(event) {
      //event.stopPropagation();
      if ((event.key == "Enter" && (event.ctrlKey == true || jQuery(event.target).is('h1, h2, span')))
          || (event.key == "s" && event.ctrlKey == true)) {
        SignupPageControls.text_submit(event.target)
        event.preventDefault();
      }
      if (event.key == "Escape") {
        SignupPageControls.element_reset(event.target);
        jQuery(event.target).blur();
      }
    });
    editables.on('paste', function(event) {
      event.preventDefault();
      let plain_data = "";
      let html_data = event.originalEvent.clipboardData.getData("text/html")
      if (html_data != "") {
        html_data = html_data.replaceAll("\n", "").replaceAll("<br>", "\n");
        let match = html_data.match(/<!--StartFragment-->(.+)<!--EndFragment-->/s);
        match && (html_data = '<div>'+match[1]+'</div>');
        plain_data = jQuery(html_data).text().replaceAll("\n","<br>");
      } else {
        plain_data = event.originalEvent.clipboardData.getData("text/plain");
      }
     
      let change_element = SignupAdminControls.replace_selection(plain_data);
      if (change_element == null) {
        selection_node = SignupPageControls.current_selection;
        if (!selection_node) return;
        if (selection_node.is('.editable')) {
          selection_node.html(replacement);
        } else {
          selection_node = selection_node.find('.editable');
          if (selection_node.length != 1) return;
          selection_node.html(replacement);
        }
      }

      SignupAdminControls.element_change(change_element);
    });
    editables.dblclick(function(event){
      event.stopPropagation() // prevent page from closing when double clicking editable text to seelect word etc.
    });
  }

  // Settings
  static initSettings(element) {
    let checkboxes = element.find('.item-checkbox');
    checkboxes.change(function(event) {
      SignupPageControls.text_submit(event.target);
    })
  }

  // Reset element to last save
  static element_reset(element) {
    element = jQuery(element);
    let ref = element.attr('edit-ref');
    let history = SignupAdminControls.editables_history[ref][0];
    element.html(history.replaceAll('\n', '<br>'));
    element.removeClass('changed');
    element.next('button').remove();
  }

  // Helper method for posting to infosys
  static post(slug, data, success) {
    console.log(data);
    jQuery.ajax({
      type: "POST",
      dataType: 'json',
      url: "/signup/pages/"+slug,
      data: data,
      success: function(response) {
        if (!response.success) {
          alert("Der skete en fejl i kommunikationen med infosys");
          return;  
        }
        success();
      },
      error: function(request, status, error) {
        alert("Der skete en fejl i kommunikationen med infosys");
      }
    })
  }

  // Submit text update
  static text_submit(element) {
    element = jQuery(element); // Make sure we have a jQuery element
    if (element.attr('type') != 'checkbox' && !SignupAdminControls.has_changed(element)) return; // Don't do anything if element hasn't changed

    let match = element.attr('class').match(/lang-(\w{2})/)
    let lang = match ? match[1] : 'none';
    let id = element.closest('fieldset').attr('id');

    let type = '';
    switch (true) {
      case element.hasClass('page-slug'):
        id = 'page:'+element.closest('.nav-button').attr('page-id');
        type = 'slug';
        break;
      case element.hasClass('page-title'):
        type = 'title';
        break;
      case element.hasClass('section-headline'):
        type = 'headline';
        break;
      case element.hasClass('item'):
        type = 'item';
        break;
      case element.hasClass('item-checkbox'):
        type = 'setting';
        break;
      case element.hasClass('infosys-id'):
        type = 'infosys_id';
        break;
      case element.hasClass('option'):
        type = 'option';
        break;
      case element.hasClass('option-value'):
        type = 'value';
        break;
      case element.hasClass('module-id'):
        type = 'module_id';
        break;
                
      default:
        type = 'unknown';
        break;
    }

    let index = {}
    id.split('--').forEach(element => {
      let match = element.match(/(\w+):([\w\-]+)/);
      index[match[1]] = match[2];
    });

    let data = {};
    index.page    && (data.page_id = index.page);
    index.section && (data.section_id = index.section);
    index.item    && (data.item_id = index.item);
    index.option  && (data.option_id = index.option);

    data.lang = lang;
    data.type = type;
    data.text = SignupAdminControls.get_text(element);
    if (type == 'setting') {
      data.setting = element.attr('setting');
      data.text = element.prop('checked') ? 'true' : 'false';
    }

    this.post('edit-text', data, function() {
      SignupAdminControls.reset_history(element);
    });
  }

  //---------------------------------------------------------------------------------
  // Add elements
  //---------------------------------------------------------------------------------
  // Section
  static add_section() {
    if (!this.current_selection) return;

    let page = this.current_selection.closest('fieldset.signup-page');
    if (!page) return;
    let page_id = page.attr('id').match(/page:(\w+)/)[1];
    
    let new_section = jQuery('<fieldset class="signup-page-section"></fieldset>');
    
    let legend = jQuery('<legend></legend>');
    new_section.append(legend);

    let headline_en = 'New Section';
    let headline_da = 'Ny Sektion';
    new_section.append(`
      <div class="headline-wrapper selectable lang-en">
        <i class="icon-uk"></i>
        <h2 class="section-headline lang-en editable">${headline_en}</h2>
      </div>
      <div class="headline-wrapper selectable lang-da">
        <i class="icon-dk"></i>
        <h2 class="section-headline lang-da editable">${headline_da}</h2>
      </div>
    `);

    let placement_section = this.current_selection.closest('fieldset.signup-page-section');
    let index = 0;
    let insert = null
    if (placement_section.length) { // Do we have a section selected (or anything inside)
      let id = placement_section.attr('id');
      let match = parseInt(id.match(/page:[\w\-]+--section:(\d+)/)[1]);
      index = isNaN(match) ? 0 : match + 1;
      insert = function () {
        new_section.insertAfter(placement_section);
        // Update id of all following sections
        let current = new_section
        while ((current = current.next()).length > 0) {
          index++;
          current.attr('id', 'page:'+page_id+'--section:'+index);
          current.children('legend').text('Section : '+index);
        }
      };
    } else {
      index = page.children('fieldset').length;
      insert = function () {page.append(new_section);};
    }
    new_section.attr('id', 'page:'+page_id+'--section:'+index);
    legend.text('Section : '+index);

    let data = {
      type: "section",
      page_id: page_id,
      section_id: index,
      headline: {
        en: headline_en,
        da: headline_da,
      },
    }

    this.post('add-element', data, function() {
        insert();
        SignupPageControls.init_element(new_section);
    });
  }
  // Insert Item
  static insert_item(type) {
    if (!this.current_selection) return;

    let section = this.current_selection.closest('fieldset.signup-page-section');
    if (!section) return;
    let page_id = section.attr('id').match(/page:(\w+)/)[1];
    let section_id = section.attr('id').match(/section:(\w+)/)[1];
    let item_id = 0;
    let infosys_id = 'unknown';
    
    // Special behavior for paragraph and list
    if (type == 'paragraph' || type == 'list') infosys_id = undefined;

    let new_item = jQuery('<fieldset class="signup-page-item" item-type="'+type+'"></fieldset>');
    let legend = jQuery('<legend></legend>');
    legend.append(type);
    if (infosys_id != undefined) {
      legend.append(' : <span class="infosys-id editable">'+infosys_id+'<span>');
    }
    new_item.append(legend);

    let text_en = 'New Item';
    let text_da = 'Nyt Element';
    new_item.append(`
      <div class="item-wrapper selectable lang-en">
        <i class="icon-uk"></i>
        <p class="item lang-en editable">${text_en}</p>
      </div>
      <div class="item-wrapper selectable lang-da">
        <i class="icon-dk"></i>
        <p class="item lang-da editable">${text_da}</p>
      </div>
    `);

    let placement_item = this.current_selection.closest('fieldset.signup-page-item');
    item_id = 0;
    if (placement_item.length) { // Do we have an item selected (or anything inside)
      let id = placement_item.attr('id');
      let match = parseInt(id.match(/page:[\w\-]+--section:\d+--item:(\d+)/)[1]);
      item_id = isNaN(match) ? 0 : match + 1;
    } else {
      placement_item = section.find('.headline-wrapper').last();
    }
    new_item.attr('id', section.attr('id')+'--item:'+item_id);

    let data = {
      type: type,
      page_id: page_id,
      section_id: section_id,
      item_id: item_id,
      text: {
        en: text_en,
        da: text_da,
      },
    }
    if (infosys_id != undefined) {
      data.infosys_id = infosys_id;
    }

    this.post('add-element', data, function() {
        if(placement_item.length) {
          new_item.insertAfter(placement_item);
        }
        // Update all following item's IDs
        let current = new_item
        let index = item_id;
        while ((current = current.next()).length > 0) {
          index++;
          current.attr('id', 'page:'+page_id+'--section:'+section_id+'--item:'+index);
        }
        SignupPageControls.init_element(new_item);
    });
  }

  // Add option
  static add_option () {
    if (!this.current_selection) return;

    let item = this.current_selection.closest('fieldset.signup-page-item');
    if (!item) return;
    let page_id = item.attr('id').match(/page:(\w+)/)[1];
    let section_id = item.attr('id').match(/section:(\d+)/)[1];
    let item_id = item.attr('id').match(/item:(\d+)/)[1];
    let option_id = 0;
    let value = 'unknown';
    
    let new_option = jQuery('<fieldset class="signup-page-option"></fieldset>');
    let legend = jQuery('<legend>option : </legend>');
    legend.append('<span class="option-value editable">'+value+'<span>');

    new_option.append(legend);

    let text_en = 'New Option';
    let text_da = 'Ny Mulighed';
    new_option.append(`
      <div class="option-wrapper selectable lang-en">
        <i class="icon-uk"></i>
        <p class="option lang-en editable">${text_en}</p>
      </div>
      <div class="option-wrapper selectable lang-da">
        <i class="icon-dk"></i>
        <p class="option lang-da editable">${text_da}</p>
      </div>
    `);

    let insert = null
    let placement_option = this.current_selection.closest('fieldset.signup-page-option');
    if (placement_option.length) { // Do we have an option selected (or anything inside)
      let id = placement_option.attr('id');
      let match = parseInt(id.match(/page:[\w\-]+--section:\d+--item:\d+--option:(\d+)/)[1]);
      option_id = isNaN(match) ? 0 : match + 1;
      insert = function () {
        new_option.insertAfter(placement_option);
        // Update all following option's IDs
        let current = new_option
        let index = option_id;
        while ((current = current.next()).length > 0) {
          index++;
          current.attr('id', 'page:'+page_id+'--section:'+section_id+'--item:'+option_id+'--option:'+index);
        }
      };
    } else {
      option_id = item.children('fieldset').length;
      insert = function () {item.append(new_option);};
    }

    new_option.attr('id', item.attr('id')+'--option:'+option_id);

    let data = {
      type: 'option',
      page_id: page_id,
      section_id: section_id,
      item_id: item_id,
      option_id: option_id,
      value: value,
      text: {
        en: text_en,
        da: text_da,
      },
    }

    this.post('add-element', data, function() {
        insert();
        SignupPageControls.initEditables(new_option);
    });
  }
}