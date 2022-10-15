"using strict";

jQuery(function() {
  SignupAdminControls.init();
});

// TODO set default

class SignupAdminControls {
  static selection_string = "fieldset, .selectable";
  
  static current_selection = null;
  static current_hover = null;
  static editables_history = [];

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
      need: 'item:radio',
    },
    add$list: {
      text: "Tilføj liste",
      need: 'section',
    },
  }
  
  static init() {
    // Selection
    this.initSelectables(jQuery(':root'));
    this.initEditables(jQuery(':root'));
    this.initSettings(jQuery(':root'));

    // Page open/close
    jQuery('.fold-button').click(function(event){
      jQuery(event.target).closest('div.page-wrapper').toggleClass('closed');
    });
    jQuery('.page-wrapper').dblclick(function(event){
      jQuery(event.delegateTarget).toggleClass('closed');
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
      menuitem.attr('need', def.need);
      context_menu.append(menuitem);
    }
    jQuery('body').append(context_menu);

    // Opening context menu
    jQuery('#page-admin-container').contextmenu(function(event) {
      if(jQuery(event.target).hasClass('editable')) return true;

      // Filter out disabled actions
      let selection = SignupAdminControls.current_selection;
      let selected_id = selection ? selection.closest('fieldset').attr('id') : "";
      let selected_type = selection ? selection.closest('fieldset.signup-page-item').attr('item-type') : "";
      context_menu.find('.menu-item').each(function (){
        let element = jQuery(this)
        let need = element.attr('need');
        if (need) {
          let [item, type] = need.split(":")
          if (selected_id.includes(item) && (!type || type == selected_type)) {
            element.show();
          } else {
            element.hide();
          }
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
        SignupAdminControls.add_section();
        return;
      }
      if (action == 'add' && type == 'radio_option') {
        SignupAdminControls.add_option();
        return;
      }
      if (action == 'add') {
        SignupAdminControls.insert_item(type);
      } 
    })
  }

  // Selection
  static initSelectables(element) {
    // Finde selectables in element (including the element itself)
    let selectables = element.find(this.selection_string)
    if (element.is(this.selection_string)) {
      selectables.add(element);
    }

    // Select item when clicked
    selectables.click(function(event) {
      SignupAdminControls.setSelection(event.target);
    })

    // Highlight selectables when hovered 
    selectables.mouseenter(function(event){
      SignupAdminControls.setSelection(event.target, 'hovering', 'current_hover');
    });
    selectables.mouseleave(function(event){
      SignupAdminControls.setSelection(null, 'hovering', 'current_hover');
      jQuery(event.relatedTarget).trigger('mouseenter');
    });
  }

  static setSelection(element, css_class = 'selected', storage = 'current_selection') {
    this[storage] && this[storage].removeClass(css_class);
    if (!element) { // We are just unsetting the selection
      this[storage] == null;  
      return;
    }
    this[storage] = jQuery(element).closest(SignupAdminControls.selection_string);
    this[storage].addClass(css_class);
    this[storage].context = document;
  }

  // Editables
  static initEditables(element) {
    let editables = element.find('.editable').attr('contentEditable', true);
    editables.each(function() {
      let element = jQuery(this);
      let history = SignupAdminControls.editables_history;
      let ref = "" + history.length;
      element.attr('edit-ref', ref);
      history[ref] = [SignupAdminControls.getText(element)];
    });
    editables.on('input', function(event) {
      SignupAdminControls.element_change(event.target);
    });
    editables.keydown(function(event) {
      //event.stopPropagation();
      if ((event.key == "Enter" && (event.ctrlKey == true || jQuery(event.target).is('h1, h2, span')))
          || (event.key == "s" && event.ctrlKey == true)) {
        SignupAdminControls.text_submit(event.target)
        event.preventDefault();
      }
      if (event.key == "Escape") {
        SignupAdminControls.element_reset(event.target);
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
     
      SignupAdminControls.replace_selection(plain_data);
      SignupAdminControls.element_change(event.target);
    });
    editables.dblclick(function(event){
      event.stopPropagation() // prevent page from closing when double clicking editable text to seelect word etc.
    });
  }

  // Settings
  static initSettings(element) {
    let checkboxes = element.find('.item-checkbox');
    checkboxes.change(function(event) {
      SignupAdminControls.text_submit(event.target);
    })
  }

  static replace_selection (replacement) {
    let selection = window.getSelection().getRangeAt(0);
    let selection_node = selection.commonAncestorContainer;
    if (selection_node.nodeName == '#text' && jQuery(selection_node).closest('.editable').length == 1) {
      let text = selection_node.data;
      let start = selection.startOffset;
      let end = selection.endOffset;
      selection_node.data = text.substring(0,start) + replacement + text.substring(end);
      selection_node = jQuery(selection_node).closest('.editable');
    } else if(jQuery(selection_node).hasClass('editable')) {
      // Get the start of selection
      let start_node = selection.startContainer;
      let start_index;
      if (start_node == selection_node) {
        start_index = selection.startOffset;
      } else {
        start_index = Array.from(selection_node.childNodes).indexOf(start_node) + 1;
        start_node.data = start_node.data.substring(0, selection.startOffset);
      }
      // Get the end of selection
      let end_node = selection.endContainer;
      let end_index;
      if (end_node == selection_node) {
        end_index = selection.endOffset;
      } else {
        end_index = Array.from(selection_node.childNodes).indexOf(end_node);
        end_node.data = end_node.data.substring(selection.endOffset);
      }

      // Remove any nodes completely inside selection
      for(let i = start_index; i < end_index; i++) {
        selection_node.removeChild(selection_node.childNodes[start_index]);
      }

      let fragment = new DocumentFragment();
      let replacement_sections = replacement.split('<br>');
      fragment.append(replacement_sections[0]);
      for(let i = 1; i < replacement_sections.length; i++) {
        fragment.append(document.createElement('br'));
        fragment.append(replacement_sections[i]);
      }
      
      // Insert replacement
      if (selection_node.childNodes.length > start_index) {
        selection_node.insertBefore(fragment, selection_node.childNodes[start_index]);
      } else {
        selection_node.append(fragment);
      }
    } else {
      selection_node = SignupAdminControls.current_selection;
      if (!selection_node) return;
      if (selection_node.is('.editable')) {
        selection_node.html(replacement);
      } else {
        selection_node = selection_node.find('.editable');
        if (selection_node.length != 1) return;
        selection_node.html(replacement);
      }
    }
    SignupAdminControls.element_change(selection_node);
  } 

  // helper function to get text but preserve line breaks
  static getText(element) {
    let html = element.html();
    html = html.replaceAll("<br>", "\n");
    return jQuery('<div>'+html+'</div>').text();
  }

  // Element has changed
  static element_change(ele) {
    let element = jQuery(ele);

    if (this.getText(element) != this.editables_history[element.attr('edit-ref')][0]) {
      element.addClass('changed');
      if (!element.next().is('button')) {
        let button = jQuery('<button class="text-submit">Gem</button>');
        element.after(button);
        button.click(function() {
          SignupAdminControls.text_submit(jQuery(this).prev());
        });
      }
    } else {
      element.removeClass('changed');
      element.next('button').remove();
    }
  }

  // Reset element to last save
  static element_reset(element) {
    element = jQuery(element);
    let ref = element.attr('edit-ref');
    let history = this.editables_history[ref][0];
    element.html(history.replaceAll('\n', '<br>'));
    element.removeClass('changed');
    element.next('button').remove();
  }

  // Helper method for posting to infosys
  static post(slug, data, success) {
    console.log(data);
    jQuery.ajax({
      type: "POST",
      url: "/signup/pages/"+slug,
      data: data,
      success: success,
      error: function(request, status, error) {
        alert("Der skete en fejl i kommunikationen med infosys");
      }
    })
  }

  // Submit text update
  static text_submit(element) {
    !(element instanceof jQuery) && (element = jQuery(element)); // Make sure we have a jQuery element
    if (element.attr('type') != 'checkbox' && this.getText(element) == this.editables_history[element.attr('edit-ref')][0]) return; // Don't do anything if element hasn't changed

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
    data.text = this.getText(element);
    if (type == 'setting') {
      data.setting = element.attr('setting');
      data.text = element.prop('checked') ? 'true' : 'false';
    }

    this.post('edit-text', data, function() {
      let ref = element.attr('edit-ref');
      SignupAdminControls.editables_history[ref] = [SignupAdminControls.getText(element)];
      element.removeClass('changed');
      element.next('button').remove();
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
        SignupAdminControls.initEditables(new_section);
        SignupAdminControls.initSelectables(new_section);
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

    let insert = null
    let placement_item = this.current_selection.closest('fieldset.signup-page-item');
    if (placement_item.length) { // Do we have an item selected (or anything inside)
      let id = placement_item.attr('id');
      let match = parseInt(id.match(/page:[\w\-]+--section:\d+--item:(\d+)/)[1]);
      item_id = isNaN(match) ? 0 : match + 1;
      insert = function () {
        new_item.insertAfter(placement_item);
        // Update all following item's IDs
        let current = new_item
        let index = item_id;
        while ((current = current.next()).length > 0) {
          index++;
          new_item.attr('id', 'page:'+page_id+'--section:'+section_id+'--item:'+index);
        }
      };
    } else {
      item_id = section.children('fieldset').length;
      insert = function () {section.append(new_item);};
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
        insert();
        SignupAdminControls.initEditables(new_item);
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
        SignupAdminControls.initEditables(new_option);
    });
  }
}