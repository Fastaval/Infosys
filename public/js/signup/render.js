"using strict";

class InfosysSignupRender {
  static render_element(item, lang, config) {
    let html;
    item.processed = InfosysTextPreprocessor.process_text(item.text[lang]);
    
    if(typeof this['render_'+item.type] === 'function') {
      html = this['render_'+item.type](item, lang);
    } else {
      html = this.render_unknown(item.processed, item.type)
    }
    
    // Convert html to jQuery
    let parsed = html;
    if(!(parsed instanceof jQuery)) {
      parsed = jQuery(jQuery.parseHTML(html.trim()));
    }

    // Add extra attributes
    if(item.required || item.required_if) {
      if (item.required) {
        parsed.addClass('required');
        parsed.find('input').attr('required', true);
      }

      // Add error text when input is empty/not selected
      if(!item.errors) item.errors = {};
      if(!item.errors.required) {
        item.errors.required = config.errors.required;
      }
    }
    if(item.excludes) {
      // Add error text when input is empty/not selected
      if(!item.errors) item.errors = {};
      if(!item.errors.excludes) {
        item.errors.excludes = config.errors.excludes;
      }
    }
    if (item.autocomplete && item.autocomplete.mode == "exhaustive") {
      // Add error text when input value is not on the exhaustive list
      if(!item.errors) item.errors = {};
      if(!item.errors.not_on_list) {
        item.errors.not_on_list = config.errors.not_on_list;
      }
    }
    if(item.no_submit) {
      parsed.find('input').attr('no-submit', true);
    }
    if(item.no_load) {
      parsed.find('input').attr('no-load', true);
    }
    if (item.errors) {
      for(const error in item.errors) {
        let error_div = jQuery('<div class="error-text" error-type="'+error+'"></div>');
        let error_text = InfosysTextPreprocessor.process_text(item.errors[error][lang]);
        error_div.html(error_text);
        error_div.hide();
        if(item.type == 'checkbox') {
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

  static render_paragraph(item, lang) {
    let paragraph = jQuery(`<p>${item.processed}</p>`)
    if (item.text_id) paragraph.attr('id', item.text_id);
    return paragraph;
  }

  static render_checkbox(item) {
    let wrapper = jQuery('<div class="input-wrapper input-type-checkbox"></div>')
    wrapper.append(`
      <input type="checkbox" id="${item.infosys_id}" name="${item.infosys_id}">
      <label for="${item.infosys_id}">${item.processed}</label>
    `);
    return wrapper;
  }

  static render_text_input(item) {
    item.processed != "" && (item.processed += ":");
    let wrapper = jQuery('<div class="input-wrapper input-type-text"></div>');
    let input_id = item.infosys_id;
    let extra = "";

    if (item.autocomplete) {
      wrapper.addClass('autocomplete');
      wrapper.append('<div class="autocomplete-list"></div>');
      wrapper.attr('autocomplete-list', item.autocomplete.list);
      if (item.autocomplete.mode == "exhaustive") {
        wrapper.append(`<input type="hidden" id="${item.infosys_id}">`);
        input_id = item.infosys_id + "-display";
        extra = 'no-submit="true" no-load="true" autocomplete="off"';
      }
    }

    if (item.maxlength) {
      extra += ` maxlength="${item.maxlength}"`
    }

    wrapper.append(`
        <label for="${input_id}">${item.processed}</label>
        <input type="text" id="${input_id}" name="${input_id}" ${extra}>
    `);

    return wrapper;
  }

  static render_telephone(item) {
    item.processed != "" && (item.processed += ":");
    return `
      <div class="input-wrapper input-type-tele">
        <label for="${item.infosys_id}">${item.processed}</label>
        <input type="tel" id="${item.infosys_id}" name="${item.infosys_id}">
      </div>
    `;
  }

  static render_date(item) {
    item.processed != "" && (item.processed += ":");
    return `
      <div class="input-wrapper input-type-date">
        <label for="${item.infosys_id}">${item.processed}</label>
        <input type="date" id="${item.infosys_id}" name="${item.infosys_id}">
      </div>
    `;
  }

  static render_email(item) {
    item.processed != "" && (item.processed += ":");
    return `
      <div class="input-wrapper input-type-email">
        <label for="${item.infosys_id}">${item.processed}</label>
        <input type="email" id="${item.infosys_id}" name="${item.infosys_id}">
      </div>
    `;
  }

  static render_radio(item, lang) {
    let div = jQuery('<div class="input-wrapper input-type-radio"></div>');
    div.append(`<p>${item.processed}</p>`);
    let hidden = jQuery(`<input type="hidden" id="${item.infosys_id}">`);

    item.options.forEach(function(element, index)  {
      div.append(this.render_radio_option(item.infosys_id, index, element, lang));
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

  static render_select(item, lang) {
    item.processed != "" && (item.processed += ":");
    let wrapper = jQuery('<div class="input-wrapper input-type-select"></div>');

    wrapper.append(`<label for="${item.infosys_id}">${item.processed}</label>`);
    
    let select = jQuery(`<select id="${item.infosys_id}" name="${item.infosys_id}"></select>`);
    wrapper.append(select);

    if (item.options && Array.isArray(item.options)) item.options.forEach( function (option) {
      select.append(`<option value="${option.value}">${option.text[lang]}</option>`);
    });

    if (item.range) {
      let step = item.range.step ?? 1;
      for (let val = item.range.start; val <= item.range.end; val += step) {
        select.append(`<option value="${val}">${val}</option>`);
      }
    }

    return wrapper;
  }

  static render_text_area(item) {
    item.processed != "" && (item.processed += ":");
    return `
      <div class="input-wrapper input-type-textarea">
        <label for="${item.infosys_id}">${item.processed}</label>
        <textarea id="${item.infosys_id}" name="${item.infosys_id}" rows="6"></textarea>
      </div>
    `;
  }

  static render_list(item) {
    let html = '<ul>';
    let lines = item.processed.split("<br>");
    lines.forEach(function(line) {
      line != '' && (html += '<li>' + line + '</li>');
    })
    html += '</ul>';
    return html;
  }

  static render_hidden(item) {
    let wrapper = jQuery('<div class="input-wrapper input-type-hidden"></div>');
    let input = jQuery(`<input type="hidden" id="${item.infosys_id}" text="${item.processed}" value="on">`);
    wrapper.append(input);
    return wrapper;
  }
}
