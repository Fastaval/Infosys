"using strict";

class InfosysSignupRender {
  static render_element(element, lang) {
    let html = "";
    let text = this.process_text(element.text[lang]);
    
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
    let html = `
      <div class="input-wrapper input-type-radio">
        <p>${item.text}</p>
        <input type="hidden" id="${item.id}">`;
    item.options.forEach(function(element, index)  {
      html += this.render_radio_option(item.id, index, element, item.lang);
    }, this);
    html += '</div>';
    return html;
  }

  static render_radio_option(id, index, element, lang) {
    let text = this.process_text(element.text[lang])
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

  // Preprocess text
  static process_text(text) {
    text = jQuery('<div>'+text+'</div>').text(); //strip any HTML
    for (let match of text.matchAll(/\[(\w+)\](.+?)\[\/\1\]/g)) {
      switch (match[1]) {
        case "email":
          text = text.replace(match[0], '<a href="mailto:'+match[2]+'">'+match[2]+'</a>');
          break;
      
        default:
          console.log("Unknown token", match);
          break;
      }
    }
    text = text.replaceAll("\n", "<br>");
    
    return text;
  }

}
