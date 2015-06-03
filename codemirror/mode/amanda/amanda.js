CodeMirror.defineSimpleMode("amandamode", {
  // The start state contains the rules that are intially used
  start: [
    // The regex matches the token, the token property contains the type
    {regex: /"(?:[^\\]|\\.)*?"/, token: "string"},
    {regex: /'(?:[^\\]|\\.)*?'/, token: "string"},
    // Rules are matched in the order in which they appear, so there is
    // no ambiguity between this one and the one above
    {regex: /(?:where|if|else|True|False|otherwise)\b/,
     token: "keyword"},
     {regex: /(?:abs|round|neg|sqrt|code|decode|ftoa|itoa|atof|atoi|isupper|islower|isspace|fst|snd|min2|max2|exp|log|cos|sin|pi|atan|time|timedate|#|hd|tl|min|max|sum|prod|take|drop|takewhile|filter|and|or|member|empty|merge|concat|zip|zip2|zip3|split|splitwhile|sort|mergeSort|reverse|nodup|map|lines|words|unlines|coordsToString|ljustify|rjustify|cjustify|rep|nat|nats|gennat|gennats|fwrite|fappend|fread)\b/,
     token: "functionname"},
    //{regex: /true|false|null|undefined/, token: "atom"},
    {regex: /0x[a-f\d]+|[-+]?(?:\.\d+|\d+\.?\d*)(?:e[-+]?\d+)?/i, token: "number"},
    {regex: /\|\|.*/, token: "comment"},
	
	// You can match multiple tokens at once. Note that the captured
    // groups must span the whole string in this case
	{regex: /([\w]+)([^=]*)=/, token: ["functionname", "variable"]},
	
    {regex: /(\+|-|\*|\/\\|\^|\/|\\\/|mod|%|=|~|~=|>|<|>=|<=|\+\+|--|:|::)/, token: "operator"},
    // indent and dedent properties guide autoindentation
    {regex: /[\{\[\(]/, indent: true},
    {regex: /[\}\]\)]/, dedent: true},
    {regex: /[a-z$][\w$]*/, token: "variable"},
    // You can embed other modes with the mode property. This rule
    // causes all code between << and >> to be highlighted with the XML
    // mode.
    //{regex: /<</, token: "meta", mode: {spec: "xml", end: />>/}}
  ],
  // The multi-line comment state.
  comment: [
    //{regex: /.*?\*\//, token: "comment", next: "start"},
    //{regex: /.*/, token: "comment"}
  ],
  // The meta property contains global information about the mode. It
  // can contain properties like lineComment, which are supported by
  // all modes, and also directives like dontIndentStates, which are
  // specific to simple modes.
  meta: {
    dontIndentStates: ["comment"],
    lineComment: "//"
  }
});
