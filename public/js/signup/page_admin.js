"using strict";

jQuery(function() {
  SignupPageAdmin.init();
});


class SignupPageAdmin {
  static selection_string = "fieldset, .selectable";
  
  static current_selection = null;
  static current_hover = null;
  static editables_history = [];

  static context_menu_items = {
    add_section: {
      text: "Tilføj Sektion",
      need: 'page',
    },
    add_p: {
      text: "Tilføj Tekstelement",
      need: 'section',
    },
    add_input_text: {
      text: "Tilføj Tekst input",
      need: 'section',
    },
    add_checkbox: {
      text: "Tilføj Checkboks",
      need: 'section',
    },
  }
  
  static init() {
    let selectables = jQuery(SignupPageAdmin.selection_string)

    // Selection
    selectables.click(function(event) {
      SignupPageAdmin.setSelection(event.target);
    })

    // Hover
    selectables.mouseenter(function(event){
      SignupPageAdmin.setSelection(event.target, 'hovering', 'current_hover');
    });
    selectables.mouseleave(function(event){
      SignupPageAdmin.setSelection(null, 'hovering', 'current_hover');
      jQuery(event.relatedTarget).trigger('mouseenter');
    });

    // Editables
    this.initEditables(jQuery(':root'));

    // Language selection
    jQuery("select#lang").change(function(event){
      switch (event.target.value) {
        case "da":
          jQuery(".lang-en").hide();
          jQuery(".lang-da").show();
          break;
        case "en":
          jQuery(".lang-da").hide();
          jQuery(".lang-en").show();
          break;
        case "both":
          jQuery(".lang-da").show();
          jQuery(".lang-en").show();
          break;
      }
    });

    // Page open/close
    jQuery('.fold-button').click(function(event){
      jQuery(event.target).closest('div.page-wrapper').toggleClass('closed');
    });

    // Context menu
    let context_menu = jQuery('<div id="contextmenu" class="closed"></div>');
    for(const [id, def] of Object.entries(this.context_menu_items)){
      let menuitem = jQuery('<div class="menu-item"><div>');
      menuitem.text(def.text);
      menuitem.attr('id', id);
      menuitem.attr('need', def.need);
      context_menu.append(menuitem);
    }
    jQuery('body').append(context_menu);

    jQuery('#page-admin-container').contextmenu(function(event) {
      if(jQuery(event.target).hasClass('editable')) return true;

      context_menu.css({
        left: event.clientX,
        top: event.clientY
      });
      context_menu.removeClass('closed').addClass('open');
      let selection = SignupPageAdmin.current_selection;
      let sected_id = selection ? selection.closest('fieldset').attr('id') : "";
      context_menu.find('.menu-item').each(function (){
        let element = jQuery(this)
        let need = element.attr('need');
        if (need) {
          sected_id.includes(need) ? element.show() : element.hide();
        }
      })
      //SignupPageAdmin.setSelection(event.target);
      return false;
    })

    jQuery('html').click(function() {
      context_menu.removeClass('open').addClass('closed');
    });

    jQuery('.menu-item').click(function (event){
      let func = jQuery(event.target).attr('id');
      if(typeof SignupPageAdmin[func] === 'function') {
        SignupPageAdmin[func](event);
      }
    })
  }

  // Selection
  static setSelection(element, css_class = 'selected', storage = 'current_selection') {
    this[storage] && this[storage].removeClass(css_class);
    if (!element) { // We are just unsetting the selection
      this[storage] == null;  
      return;
    }
    this[storage] = jQuery(element).closest(SignupPageAdmin.selection_string);
    this[storage].addClass(css_class);
    this[storage].context = document;
  }

  // Editables
  static initEditables(element) {
    let editables = element.find('.editable').attr('contentEditable', true);
    editables.each(function() {
      let element = jQuery(this);
      let history = SignupPageAdmin.editables_history;
      let ref = "" + history.length;
      element.attr('edit-ref', ref);
      history[ref] = [element.text()];
    });
    editables.on('input', function(event) {
      SignupPageAdmin.element_change(event.target);
    });
    editables.keydown(function(event) {
      //event.stopPropagation();
      if (event.key == "Enter" && (event.ctrlKey == true || jQuery(event.target).is('h1, h2, span'))) {
        SignupPageAdmin.text_submit(event.target)
        event.preventDefault();
      }
      if (event.key == "Escape") {
        SignupPageAdmin.element_reset(event.target);
        jQuery(event.target).blur();
      }
    });
    editables.on('paste', function(event) {
      event.preventDefault();
      let plain_data = event.originalEvent.clipboardData.getData("text/plain");
      let selection = window.getSelection().getRangeAt(0);
      let selection_node = selection.commonAncestorContainer;
      if (selection_node.nodeName == '#text') {
        let text = selection_node.data;
        let start = selection.startOffset;
        let end = selection.endOffset;
        selection_node.data = text.substring(0,start) + plain_data + text.substring(end);
      } else {
        selection_node.innerHTML = plain_data;
      }
      SignupPageAdmin.element_change(event.target);
    });
  }

  // Element has changed
  static element_change(ele) {
    let element = jQuery(ele);

    if (element.text() != this.editables_history[element.attr('edit-ref')][0]) {
      element.addClass('changed');
      if (!element.next().is('button')) {
        let button = jQuery('<button class="text-submit">Gem</button>');
        element.after(button);
        button.click(function() {
          SignupPageAdmin.text_submit(jQuery(this).prev());
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
    element.text(history);
    element.removeClass('changed');
    element.next('button').remove();
  }

  // Helper method for posting to infosys
  static post(slug, data, success) {
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
    if (element.text() == this.editables_history[element.attr('edit-ref')][0]) return; // Don't do anything if element hasn't changed

    let match = element.attr('class').match(/lang-(\w{2})/)
    let lang = match ? match[1] : 'none';
    let id = element.closest('fieldset').attr('id');

    let type = '';
    switch (true) {
      case element.hasClass('page-header'):
        type = 'title'
        break;
      case element.hasClass('section-headline'):
        type = 'headline'
        break;
      case element.hasClass('item'):
        type = 'item'
        break;
      case element.hasClass('infosys-id'):
        type = 'infosys_id'
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

    data.lang = lang;
    data.type = type;
    data.text = element.text();

    this.post('edit-text', data, function() {
      let ref = element.attr('edit-ref');
      SignupPageAdmin.editables_history[ref] = [element.text()];
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
      <div class="headline-wrapper selectable">
        <i class="icon-uk"></i>
        <h2 class="section-headline lang-en editable">${headline_en}</h2>
      </div>
      <div class="headline-wrapper selectable">
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
        SignupPageAdmin.initEditables(new_section);
    });
  }

  // Input Text
  static add_input_text() {
    if (!this.current_selection) return;

    let section = this.current_selection.closest('fieldset.signup-page-section');
    if (!section) return;
    let page_id = section.attr('id').match(/page:(\w+)/)[1];
    let section_id = section.attr('id').match(/section:(\w+)/)[1];
    let item_id = 0;
    let type = 'text_input';
    let infosys_id = 'unknown';

    let new_item = jQuery('<fieldset class="signup-page-item"></fieldset>');
    let legend = jQuery('<legend></legend>');
    legend.append(type + ' : <span class="infosys-id editable">'+infosys_id+'<span>');
    new_item.append(legend);

    let text_en = 'New Text Input';
    let text_da = 'Nyt Tekstindput';
    new_item.append(`
      <div class="item-wrapper selectable">
        <i class="icon-uk"></i>
        <p class="item lang-en editable">${text_en}</p>
      </div>
      <div class="item-wrapper selectable">
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
        let current = new_item
        let index = item_id;
        while ((current = current.next()).length > 0) {
          index++;
          current.attr('id', 'page:'+page_id+'--section:'+section_id+'--item:'+index);
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
      infosys_id: infosys_id,
    }

    this.post('add-element', data, function() {
        insert();
        SignupPageAdmin.initEditables(new_item);
    });
  }
}