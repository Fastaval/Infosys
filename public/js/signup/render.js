"using strict";

class InfosysSignupRender {
    static render_element(element, lang) {
        let html = "";
        let text = this.process_text(element.text[lang]);
        switch (element.type) {
            case "p":
                html = this.render_paragraph(text);
                break;
            case "checkbox":
                html = this.render_checkbox(text, element.infosys_id);
                break;
            
            default:
                html = this.render_unknown(text, element.type)
                break;
        }
        return html;
    }

    static render_unknown(text, type) {
        return "<p class='unknown'><strong>Unknown element of type: "+ type + "</strong><br>" + text +"</p>";
    }

    static render_paragraph(text) {
        return "<p>" + text + "</p>";
    }

    static render_checkbox(text, id) {
        return '<input type="checkbox" id="'+id+'"><label for="'+id+'">'+text+'</label>';
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
        
        return text;
    }

}
