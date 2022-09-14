"using strict";

class InfosysTextPreprocessor {
  // Preprocess text
  static process_text(text) {
    text = jQuery('<div>'+text+'</div>').text(); //strip any HTML
    for (let match of text.matchAll(/\[(\w+)(?:=([^\]]+))?\](.*?)\[\/\1\]/gs)) {
      switch (match[1]) {
        case "email":
          text = text.replace(match[0], '<a href="mailto:'+match[3]+'">'+match[3]+'</a>');
          break;

        case "b":
          text = text.replace(match[0], '<strong>'+match[3]+'</strong>');
          break;

        case "url":
          let url = match[2].replaceAll('"','\\"');
          text = text.replace(match[0], '<a href="'+url+'">'+match[3]+'</a>');
          break;

        case "color":
          let [bgcolor, color] = match[2].replaceAll(/[^\w#,]/g, "").split(",",2) ;
          bgcolor = bgcolor ?? "white";
          color = color ?? "black";
          text = text.replace(match[0], '<span style="background-color:'+bgcolor+';color:'+color+';">'+match[3]+'</span>');
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