"using strict";

class InfosysTextPreprocessor {
  // Preprocess text
  static process_text(text) {
    text = jQuery('<div>'+text+'</div>').text(); //strip any HTML
    for (let match of text.matchAll(/\[(\w+)\](.*?)\[\/\1\]/gs)) {
      switch (match[1]) {
        case "email":
          text = text.replace(match[0], '<a href="mailto:'+match[2]+'">'+match[2]+'</a>');
          break;

        case "b":
          text = text.replace(match[0], '<strong>'+match[2]+'</strong>');
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