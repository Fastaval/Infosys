"using strict";

jQuery(function() {
  SignupAdminRender.init();
});

class SignupAdminRender {
  static pages;
  static page_keys;

  static main_content;
  static main_container;
  static nav;
  static page_container;

  static icon_names = {
    en: 'uk',
    da: 'dk',
  };

  static init() {
    this.main_content = jQuery('.content-container');

    this.main_container = jQuery('<div id="signup-admin-container"></div>');
    this.main_container.append('<h1>Administration af tilmeldingssider</h1>')
    this.main_content.append(this.main_container);

    this.nav = jQuery("<nav id='signup-navigation'></nav>");
    this.main_container.append(this.nav);

    this.page_container = jQuery('<div id="signup-pages-container"></div>');
    this.main_container.append(this.page_container);

    this.load_page_list();
  }

  static load_page_list () {
    jQuery.getJSON(
      "/api/signup/pagelist",
      function (pages) {
        SignupPageControls.pagelist_ready(pages);
        SignupAdminRender.parse_page_list(pages);
      }
    ).fail(function () {
        SignupAdminControls.com_error();
    });
  }

  static parse_page_list(pages) {
    this.pages = pages;
    
    // Sort page keys by ordering
    let keys = Object.keys(pages);
    this.page_keys = keys.sort((a,b) => {
      return pages[a].order - pages[b].order;
    })

    // Render navigation
    SignupAdminRender.navigation();
    // Setup navigation functionality
    this.nav.find("div.nav-button").click((evt) => {
      let key = evt.delegateTarget.getAttribute("page-id");
      SignupPageControls.nav_click(key);
    })
    // Fully load pages
    setTimeout( () => {this.load_pages(keys)});
  }

  static load_pages(keys) {
    keys.forEach( key => {
      jQuery.getJSON(
        "/api/signup/page/"+key,
        function (page) {
          SignupAdminRender.pages[key] = page;
          SignupAdminRender.page(key);
          SignupAdminRender.page_ready(key);
        }
      ).fail(function () {
          SignupAdminControls.com_error();
      });
    })
  }

  static navigation() {
    this.page_keys.forEach( key => {
      let button = 
        "<div page-id='"+key+"' class='nav-button loading'>"+
        "  <div class='lang-en'><i class='icon-uk'></i><span class='page-slug lang-en editable'>"+SignupAdminRender.pages[key].slug.en+"</span></div>"+
        "  <div class='lang-da'><i class='icon-dk'></i><span class='page-slug lang-da editable'>"+SignupAdminRender.pages[key].slug.da+"</span></div>"+
        "</div>";
        SignupAdminRender.nav.append(button);    
    })
    SignupPageControls.init_element(this.nav);
  }

  static page(key) {
    let page_element = jQuery('<fieldset></fieldset>');
    let page_dom_id = 'page:'+key;
    page_element.attr('id', page_dom_id);
    page_element.addClass('signup-page');

    let legend = jQuery('<legend>Page : '+key+'</legend>');
    page_element.append(legend);

    let page = this.pages[key];
    for(const lang in page.title) {
      let title_wrapper = jQuery('<div class="title-wrapper selectable lang-'+lang+'"></div>');
      title_wrapper.append('<i class="icon-'+this.icon_names[lang]+'"></i>');
      title_wrapper.append('<h1 class="page-title lang-'+lang+' editable">'+page.title[lang]+'</h1>');
      page_element.append(title_wrapper);
    }

    for(const section_index in page.sections) {
      let section_dom_id = page_dom_id+'--section:'+section_index;
      let section_element = this.section(page.sections[section_index], section_dom_id, section_index);
      page_element.append(section_element);
    }

    page_element.hide();
    this.page_container.append(page_element);
    SignupPageControls.init_element(page_element);
    if (key == SignupPageControls.current_page) SignupPageControls.show_page(key);
  }

  static section(section, dom_id, index) {
    // Section
    let section_element = jQuery('<fieldset></fieldset>');
    section_element.attr('id', dom_id);
    section_element.addClass('signup-page-section');
    
    // Legend
    let legend = jQuery('<legend></legend>');
    if (section.module) {
      legend.append('Module : <span class="module-id editable">'+section.module+'</span>');
    } else {
      legend.append("Section : "+index);
    }
    section_element.append(legend);

    // Headline
    for(const lang in section.headline) {
      let headline_wrapper = jQuery('<div class="headline-wrapper selectable lang-'+lang+'"></div>');
      headline_wrapper.append('<i class="icon-'+this.icon_names[lang]+'"></i>');
      headline_wrapper.append('<h2 class="section-headline lang-'+lang+' editable">'+section.headline[lang]+'</h2>');
      section_element.append(headline_wrapper);
    }

    // Items
    for(const key in section.items) {
      let item_dom_id = dom_id+'--item:'+key;
      let item_element = this.item(section.items[key], item_dom_id);
      section_element.append(item_element);
    }

    return section_element;
  }

  static item(item, dom_id) {
    // Item
    let item_element = jQuery('<fieldset></fieldset>');
    item_element.attr('id', dom_id);
    item_element.addClass('signup-page-item');
    item_element.attr('item-type', item.type);

    // Legend
    let legend = jQuery('<legend></legend>');
    legend.append(item.type);
    if (item.infosys_id) { // Infosys ID
      legend.append(' : <span class="infosys-id editable">'+item.infosys_id+'</span>');
    }
    if(['text_input', 'telephone', 'checkbox', 'date', 'email','radio'].includes(item.type)) { // Checkboxes
      let required = item.required ? 'checked="checked"' : '';
      let label_required = jQuery('<label class="checkbox-label">Påkrævet</label>');
      label_required.append('<input type="checkbox" class="item-checkbox toggle-required" setting="required" '+required+'>');
      legend.append(label_required);

      let disabled = item.disabled ? 'checked="checked"' : '';
      let label_disabled = jQuery('<label class="checkbox-label">Deaktiveret</label>');
      label_disabled.append('<input type="checkbox" class="item-checkbox toggle-disabled" setting="disabled" '+disabled+'>');
      legend.append(label_disabled);
    }
    item_element.append(legend);

    // Text
    for(const lang in item.text) {
      let text_wrapper = jQuery('<div class="item-wrapper selectable lang-'+lang+' item-type-'+item.type+'"></div>');
      text_wrapper.append('<i class="icon-'+this.icon_names[lang]+'"></i>');
      text_wrapper.append('<p class="item lang-'+lang+' editable">'+item.text[lang].replaceAll('\n', '<br>')+'</p>');
      item_element.append(text_wrapper);
    }

    // Options
    if(item.options) {
      for(const key in item.options) {
        let option_dom_id = dom_id+'--option:'+key;
        let option_element = this.option(item.options[key], option_dom_id);
        item_element.append(option_element);
      }
    }

    return item_element;
  }

  static option(option, dom_id) {
    // Option
    let option_element = jQuery('<fieldset></fieldset>');
    option_element.attr('id', dom_id);
    option_element.addClass('signup-page-option');

    // Legend
    let legend = jQuery('<legend></legend>');
    legend.append('option : <span class="option-value editable">'+option.value+'</span>');
    option_element.append(legend);

    for(const lang in option.text) {
      let text_wrapper = jQuery('<div class="option-wrapper selectable lang-'+lang+'"></div>');
      text_wrapper.append('<i class="icon-'+this.icon_names[lang]+'"></i>');
      text_wrapper.append('<span class="option lang-'+lang+' editable">'+option.text[lang]+'</span>');
      option_element.append(text_wrapper);
    }

    return option_element;
  }

  static page_ready(key) {
  }
}
