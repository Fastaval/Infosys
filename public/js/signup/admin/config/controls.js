"using strict";

jQuery(function() {
  SignupAdminControls.save_function = function(element) {
    SignupConfigControls.text_submit(element);
  }  
});

class SignupConfigControls {
  static config = {};

  static load_config(name, callBack) {
    jQuery.getJSON(
      "/api/signup/config/"+name,
      function (config) {
        SignupConfigControls.config[name] = config;
        callBack();
      }
    ).fail(function () {
        SignupAdminControls.com_error();
    });
  }

  // Reset element to last save
  static rest_input(input) {
    input = jQuery(input);
    let id = input.attr('id');
    let history = SignupAdminControls.editables_history[id][0];
    input.val(history);
    input.removeClass('changed');
    input.next('button').remove();
  }

  static text_submit(element) {
    let id = element.attr('id');
    let index = id.indexOf('::');
    let module = id.substr(0, index);
    id = id.substr(index+2);

    jQuery.ajax({
      url: `/signup/config/${module}/edit`,
      type: 'post',
      data: {
        id,
        text: element.val()
      },
      success: function(response) {
        if (response.status !== 'success') {
          SignupAdminControls.com_error();
          console.log('Com error:', response.message, response);
        }
        SignupAdminControls.reset_history(element);
      },
      fail: function() {
        SignupAdminControls.com_error();
      }
    })

    
  }
}