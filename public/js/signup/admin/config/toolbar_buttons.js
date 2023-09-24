"using strict";

SignupAdminTools.extra(function() {
  SignupConfigButtons.add_buttons();
})

class SignupConfigButtons {

  static config_select;

  static add_buttons() {
    // Language selection
    this.config_select = jQuery("<select id='config-select'></select>");
    this.config_select.append(jQuery(`<option value=''>Vælg modul</option>`));
    this.config_select.change(function(event){
      SignupConfigRender.show_config(event.target.value);
    });
    
    SignupAdminTools.toolbar.prepend(this.config_select);

    this.load_config_list();
  }

  static load_config_list () {
    jQuery.getJSON(
      "/api/signup/configlist",
      function (list) {
        list.forEach(config => {
          SignupConfigButtons.config_select.append(jQuery(`<option value='${config}'>${config}</option>`));
        });
      }
    ).fail(function () {
        SignupAdminControls.com_error();
    });
  }
}