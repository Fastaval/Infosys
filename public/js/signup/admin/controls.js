"using strict";

class SignupAdminControls {
  static editables_history = {};
  static save_function;

  static get_id_text(element) {
    let ref, text;
    if (element.is('input, textarea')) {
      ref = element.attr('id');
      text = element.val();
    } else {
      ref = element.attr('edit-ref');
      if (ref == undefined) {
        ref = "" + this.editables_history.length;
        element.attr('edit-ref', ref);
      }
      text = this.get_text(element);
    }
    return [ref, text];
  }

  static has_changed(element) {
    let [ref, text] = this.get_id_text(element);
    return this.editables_history[ref][0] != text;
  }

  static reset_history(element) {
    let [ref, text] = this.get_id_text(element);
    this.editables_history[ref] = [text];
    element.removeClass('changed');
    element.next('button').remove();
  }

  static replace_selection (replacement) {
    let selection = window.getSelection().getRangeAt(0);
    let selection_node = selection.commonAncestorContainer;
    if (selection_node.nodeName == '#text' && jQuery(selection_node).closest('.editable').length == 1) {
      let text = selection_node.data;
      let start = selection.startOffset;
      let end = selection.endOffset;
      selection_node.data = text.substring(0,start) + replacement + text.substring(end);
      selection_node = jQuery(selection_node).closest('.editable');
    } else if(jQuery(selection_node).hasClass('editable')) {
      // Get the start of selection
      let start_node = selection.startContainer;
      let start_index;
      if (start_node == selection_node) {
        start_index = selection.startOffset;
      } else {
        start_index = Array.from(selection_node.childNodes).indexOf(start_node) + 1;
        start_node.data = start_node.data.substring(0, selection.startOffset);
      }
      // Get the end of selection
      let end_node = selection.endContainer;
      let end_index;
      if (end_node == selection_node) {
        end_index = selection.endOffset;
      } else {
        end_index = Array.from(selection_node.childNodes).indexOf(end_node);
        end_node.data = end_node.data.substring(selection.endOffset);
      }

      // Remove any nodes completely inside selection
      for(let i = start_index; i < end_index; i++) {
        selection_node.removeChild(selection_node.childNodes[start_index]);
      }

      let fragment = new DocumentFragment();
      let replacement_sections = replacement.split('<br>');
      fragment.append(replacement_sections[0]);
      for(let i = 1; i < replacement_sections.length; i++) {
        fragment.append(document.createElement('br'));
        fragment.append(replacement_sections[i]);
      }
      
      // Insert replacement
      if (selection_node.childNodes.length > start_index) {
        selection_node.insertBefore(fragment, selection_node.childNodes[start_index]);
      } else {
        selection_node.append(fragment);
      }
    } else {
      return null;
    }
    return selection_node;
  }

  // Element has changed
  static element_change(element) {
    element = jQuery(element);

    if (this.has_changed(element)) {
      element.addClass('changed');
      if (!element.next().is('button')) {
        let button = jQuery('<button class="text-submit">Gem</button>');
        element.after(button);
        button.click(function() {
          SignupAdminControls.save_function(element);
        });
      }
    } else {
      element.removeClass('changed');
      element.next('button').remove();
    }
  }

  // helper function to get text but preserve line breaks
  static get_text(element) {
    let html = element.html();
    html = html.replaceAll("<br>", "\n");
    return jQuery('<div>'+html+'</div>').text();
  }

  static com_error() {
    alert(
      "Der skete en fejl i kommunikationen med Infosys\n" +
      "Dette kan være en midlertidig fejl og du er velkommen til at prøve igen.\n"+
      "Hvis fejlen fortsættter må du meget gerne kontakte admin@fastaval.dk"
    );
  }
}