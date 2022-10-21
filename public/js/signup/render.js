"using strict";

class InfosysSignupRender {
  static render_element(element, lang, config) {
    let html = "";
    let text = InfosysTextPreprocessor.process_text(element.text[lang]);
    
    if(typeof this['render_'+element.type] === 'function') {
      let item = { 
        text: text, 
        lang: lang
      }
      element.infosys_id  && (item.id = element.infosys_id);
      element.options     && (item.options = element.options);

      html = this['render_'+element.type](item);
    } else {
      html = this.render_unknown(text, element.type)
    }
    
    // Add extra attributes
    let parsed = jQuery(jQuery.parseHTML(html.trim()));
    if(element.required) {
      parsed.addClass('required');
      parsed.find('input').attr('required', true);

      // Add error text when input is empty/not selected
      if(!element.errors) element.errors = {};
      if(!element.errors.required) {
        element.errors.required = config.errors.required;
      }
    }
    if(element.excludes) {
      // Add error text when input is empty/not selected
      if(!element.errors) element.errors = {};
      if(!element.errors.excludes) {
        element.errors.excludes = config.errors.excludes;
      }
    }
    if(element.no_submit) {
      parsed.find('input').attr('no-submit', true);
    }
    if (element.errors) {
      for(const error in element.errors) {
        let error_type = error == 'required_if' ? 'required' : error;
        let error_div = jQuery('<div class="error-text" error-type="'+error_type+'"></div>');
        error_div.text(element.errors[error_type][lang]);
        error_div.hide();
        if(element.type == 'checkbox') {
          parsed.prepend(error_div);
        } else {
          parsed.append(error_div);
        }
      }
    }
    
    html = parsed.prop('outerHTML');
    
    return html;
  }

  static render_unknown(text, type) {
    return "<p class='unknown'><strong>Unknown element of type: "+ type + "</strong><br>" + text +"</p>";
  }

  static render_paragraph(item) {
    return "<p>" + item.text + "</p>";
  }

  static render_checkbox(item) {
    return `
      <div class="input-wrapper input-type-checkbox">
        <input type="checkbox" id="${item.id}">
        <label for="${item.id}">${item.text}</label>
      </div>
    `;
  }

  static render_text_input(item) {
    item.text != "" && (item.text += ":");
    return `
      <div class="input-wrapper input-type-text">
        <label for="${item.id}">${item.text}</label>
        <input type="text" id="${item.id}">
      </div>
    `;
  }

  static render_telephone(item) {
    item.text != "" && (item.text += ":");
    return `
      <div class="input-wrapper input-type-tele">
        <label for="${item.id}">${item.text}</label>
        <input type="tel" id="${item.id}">
      </div>
    `;
  }

  static render_date(item) {
    item.text != "" && (item.text += ":");
    return `
      <div class="input-wrapper input-type-date">
        <label for="${item.id}">${item.text}</label>
        <input type="date" id="${item.id}">
      </div>
    `;
  }

  static render_email(item) {
    item.text != "" && (item.text += ":");
    return `
      <div class="input-wrapper input-type-email">
        <label for="${item.id}">${item.text}</label>
        <input type="email" id="${item.id}">
      </div>
    `;
  }

  static render_radio(item) {
    let div = jQuery('<div class="input-wrapper input-type-radio"></div>');
    div.append(`<p>${item.text}</p>`);
    let hidden = jQuery(`<input type="hidden" id="${item.id}">`);

    item.options.forEach(function(element, index)  {
      div.append(this.render_radio_option(item.id, index, element, item.lang));
      if (element.default) hidden.val(element.value);
    }, this);
    div.append(hidden);
    return div.prop('outerHTML');
  }

  static render_radio_option(id, index, element, lang) {
    let text = InfosysTextPreprocessor.process_text(element.text[lang])
    let checked = element.default ? "checked" : "";
    return `
      <div class="input-wrapper input-type-radio-option">
        <input type="radio" value="${element.value}" ${checked} id="${id}-${index}" name="${id}">
        <label for="${id}-${index}">${text}</label>
      </div>
    `;
  }

  static render_text_area(item) {
    item.text != "" && (item.text += ":");
    return `
      <div class="input-wrapper input-type-textarea">
        <label for="${item.id}">${item.text}</label>
        <textarea id="${item.id}" rows="6"></textarea>
      </div>
    `;
  }

  static render_list(item) {
    let html = '<ul>';
    let lines = item.text.split("<br>");
    lines.forEach(function(line) {
      line != '' && (html += '<li>' + line + '</li>');
    })
    html += '</ul>';
    return html;
  }
}
