"using strict";

jQuery(function() {
  SignupPageAdmin.init();
});


class SignupPageAdmin {
  static current_selection = null;
  static current_hover = null;

  static context_menu_items = {
    add_section:      "Tilføj Sektion",
    add_p:            "Tilføj Tekstelement",
    add_input_text:   "Tilføj Tekst input",
    add_checkbox:     "Tilføj Checkboks",
  }
  
  static init() {
    let selectables = jQuery('fieldset, legend.selectable')

    // Selection
    selectables.click(function(event) {
      SignupPageAdmin.setSelection(event.target);
    })

    //Hover
    selectables.mouseenter(function(event){
      SignupPageAdmin.setSelection(event.target, 'hovering', 'current_hover');
    });
    selectables.mouseleave(function(event){
      SignupPageAdmin.setSelection(null, 'hovering', 'current_hover');
      jQuery(event.relatedTarget).trigger('mouseenter');
    });

    //Language selection
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
    for(const [id, text] of Object.entries(this.context_menu_items)){
      let menuitem = jQuery('<div class="menu-item"><div>');
      menuitem.text(text);
      menuitem.attr('id', id);
      context_menu.append(menuitem);
    }
    jQuery('body').append(context_menu);

    jQuery('#page-admin-container').contextmenu(function(event) {
      context_menu.css({
        left: event.clientX,
        top: event.clientY
      });
      context_menu.removeClass('closed').addClass('open');
      SignupPageAdmin.setSelection(event.target);
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

  static setSelection(element, css_class = 'selected', storage = 'current_selection') {
    if (this[storage]) {
      this[storage].removeClass(css_class);
      storage == 'current_selection' && this[storage].find('p').attr('contentEditable', null);
    }
    if (!element) { // We are just unsetting the selection
      this[storage] == null;  
      return;
    }
    this[storage] = jQuery(element).closest('fieldset, legend.selectable');
    this[storage].addClass(css_class);
    this[storage].context = document;

    storage == 'current_selection' && this[storage].find('p').attr('contentEditable', true);
  }

  static add_section(event) {
    if (!this.current_selection) return;

    let page = this.current_selection.closest('fieldset.signup-page');
    if (!page) return;
    let page_id = page.attr('id');
    
    let new_section = jQuery('<fieldset class="signup-page-section"><legend>Ny Sektion</legend></fieldset>');
    let placement_section = this.current_selection.closest('fieldset.signup-page-section');
    let index = 0;
    let insert = null
    if (placement_section.length) { // Do we have a section selected (or anything inside)
      let id = placement_section.attr('id');
      index = id.match(/\w+-section-(\d+)/)[1];
      new_section.attr('id', page_id+'-section-'+index);
      insert = function () {new_section.insertAfter(placement_section);};
    } else {
      index = page.children('fieldset').length;
      new_section.attr('id', page_id+'-section-'+index);
      insert = function () {page.append(new_section);};
    }

    jQuery.ajax({
      type: "POST",
      url: "/signup/pages/add-element",
      data: {
        type: "section",
        page_id: page_id,
        section_id: index,
        headline: {
          en: "New Section",
          da: "Ny Sektion",
        },
      },
      success: function(data) {
        console.log("Add element success", data);
        insert();
      },
      error: function(request, status, error) {
        alert("Der skete en fejl i kommunikationen med infosys");
      }
    })
  }
}