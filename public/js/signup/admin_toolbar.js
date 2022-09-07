"using strict";

jQuery(function() {
  SignupAdminTools.init();
});

class SignupAdminTools {
  static init() {
    this.toolbar = jQuery("<div id='page-admin-toolbar'></div>");

    // Language selection
    this.language = jQuery("<select id='lang'></select>");
    this.language.append(jQuery("<option value='both'>Vis Begge</option>"));
    this.language.append(jQuery("<option value='da'>Vis Dansk</option>"));
    this.language.append(jQuery("<option value='en'>Vis Engelsk</option>"));
    this.language.change(function(event){
      SignupAdminTools.lang_change(event.target.value);
    });
    this.toolbar.append(this.language);


    // Bold button
    this.bold = jQuery('<button id="bold">B</button>');
    this.bold.click(function() {
      SignupAdminTools.bold_click();
    });
    this.toolbar.append(this.bold);

    // List button
    // this.list = jQuery('<button id="list">List</button>');
    // this.list.click(function() {
    //   SignupAdminTools.list_click();
    // });
    // this.toolbar.append(this.list);

    jQuery('div.content-container').prepend(this.toolbar);
  }

  static lang_change(lang) {
    switch (lang) {
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
  }

  static wrap_selection(before, after) {
    let text = ''
    let selection = window.getSelection().getRangeAt(0);
    let selection_node = selection.commonAncestorContainer;

    // Get the start of selection
    let start_node = selection.startContainer;
    let start_index;
    if (start_node == selection_node) {
      start_index = selection.startOffset;
    } else {
      start_index = Array.from(selection_node.childNodes).indexOf(start_node) + 1;
      text += start_node.data.substring(selection.startOffset);
    }

    // Get the end of selection
    let end_node = selection.endContainer;
    let end_index;
    let end_text = '';
    if (end_node == selection_node) {
      end_index = selection.endOffset;
    } else {
      end_index = Array.from(selection_node.childNodes).indexOf(end_node);
      end_text = end_node.data.substring(0, selection.endOffset);
    }

    // Remove any nodes completely inside selection
    for(let i = start_index; i < end_index; i++) {
      let child = selection_node.childNodes[i];
      text += child.outerHTML ? child.outerHTML : child.data;
    }

    text += end_text;

    SignupAdminPages.replace_selection(before+text+after);
  }

  // static list_click() {
  //   this.wrap_selection("[list]","[/list]");
  // }

  static bold_click() {
    this.wrap_selection("[b]","[/b]");
  }
}
