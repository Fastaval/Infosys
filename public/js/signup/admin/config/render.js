"using strict";

jQuery(function() {
  SignupConfigRender.init();
});

class SignupConfigRender {
  static config_pages = {};
  static current_page;
  
  static init() {
    this.main_content = jQuery('.content-container');

    this.main_container = jQuery('<div id="signup-admin-container"></div>');
    this.main_container.append('<h1>Indstillinger for tilmeldingssider</h1>')
    this.main_content.append(this.main_container);

    this.config_container = jQuery('<div id="signup-config-container"></div>');
    this.main_container.append(this.config_container);
  }

  static show_config(name) {
    if (name == '') return;

    if (this.config_pages[name] == undefined) {
      SignupConfigControls.load_config(name, function() {
        SignupConfigRender.render_config_page(name, SignupConfigControls.config[name]);
        SignupConfigRender.show_config_page(name);
      })
    }
    this.show_config_page[name];
  }

  static show_config_page(name){
    if (this.current_page !== undefined) {
      this.current_page.hide();
    }
    this.current_page = this.config_pages[name];
    this.current_page.show();
  }

  static render_config_page(name, config) {
    let page = jQuery(`<div id='config-page-${name}'></div>`);
    this.config_container.append(page);
    this.config_pages[name] = page;
    this.render_children(config, page, name);
  }

  static render_children(item, parent, id) {
    for(const setting in item) {
      let child_id = id + '::' + setting;
      let child = item[setting];

      // Compact rendering of number setting
      let element, label, input;
      if (typeof child == 'number') {
        element = jQuery(`<div id'config-item-${child_id}'</div>`);
        label = jQuery(`<span>${setting}</span>`);
        input = jQuery(`<input type='number' id='${child_id}' value='${child}'>`);
      } else {

        // Render fieldset for normal setting or collection of settings
        element = jQuery(`<fieldset id='config-item-${child_id}'></fieldset>`);

        // Label for element
        label = jQuery(`<legend></legend>`)
        if (['en', 'da'].includes(setting)) {
          let icon = {en:'uk', da:'dk'}[setting];
          label.html(`<i class="icon-${icon}"></i>`);
        } else {
          label.text(setting);
        }

        // 
        if (typeof child == 'object') {
          parent.append(element);
          element.append(label);
          this.render_children(child, element, child_id);
          continue;
        } else {
          input = jQuery(`<textarea id='${child_id}'>${child}</textarea>`);
          input.on('input', function() {
            this.style.height = 0;
            this.style.height = this.scrollHeight + 'px';
          })
        }
      }

      parent.append(element);
      element.append(label);
      element.append(input);

      // Resize textarea to fit content
      if (input.is('textarea')) {
        input.css('width', '100%');
        input.css('height', input.prop('scrollHeight')+'px');
      }

      SignupAdminControls.reset_history(input);
      // Check for changes on input
      input.on('input', function(event) {
        SignupAdminControls.element_change(event.target);
      });

      // Events for special keys
      input.keydown(function(event) {
        //event.stopPropagation();
        if ((event.key == "Enter" && (event.ctrlKey == true || jQuery(event.target).is('input')))
            || (event.key == "s" && event.ctrlKey == true)) {
          SignupConfigControls.text_submit(jQuery(event.target))
          event.preventDefault();
        }
        if (event.key == "Escape") {
          SignupConfigControls.rest_input(event.target);
          jQuery(event.target).blur();
        }
      });
    }
  }
}