CodeMirror.defineSimpleMode("amandamode", {
  // The start state contains the rules that are intially used
  start: [
    // The regex matches the token, the token property contains the type
    {regex: /"(?:[^\\]|\\.)*?"/, token: "string"},
    {regex: /'(?:[^\\]|\\.)*?'/, token: "string"},
    // You can match multiple tokens at once. Note that the captured
    // groups must span the whole string in this case
      //{regex: /([a-z]+[a-zA-Z0-9]*)([^=]*)(=)/gmi, token: ["variable-2","","comment"]},
    //{regex: /^([a-z$\w$]+[a-zA-Z0-9$]*)\s+(\[*\(*[a-zA-Z0-9$]*\]*\)*)\s*(=)/,
    // token: ["variable-2","","comment"]},
    // Rules are matched in the order in which they appear, so there is
    // no ambiguity between this one and the one above
    {regex: /(?:where|if|else|True|False|otherwise)\b/,
     token: "keyword"},
    //{regex: /true|false|null|undefined/, token: "atom"},
    {regex: /0x[a-f\d]+|[-+]?(?:\.\d+|\d+\.?\d*)(?:e[-+]?\d+)?/i,
     token: "number"},
    {regex: /\|\|.*/, token: "comment"},
    //{regex: /\/(?:[^\\]|\\.)*?\//, token: "variable-3"},
    // A next property will cause the mode to move to a different state
    //{regex: /\/\*/, token: "comment", next: "comment"},
    //{regex: /[-+\/*=<>!]+/, token: "operator"},
    {regex: /(\+|-|\*|\/\\|\^|\/|\\\/|mod|%|=|~|~=|>|<|>=|<=|\+\+|--|:|::)/, token: "comment"},
    // indent and dedent properties guide autoindentation
    {regex: /[\{\[\(]/, indent: true},
    {regex: /[\}\]\)]/, dedent: true},
    {regex: /[a-z$][\w$]*/, token: "variable"},
    // You can embed other modes with the mode property. This rule
    // causes all code between << and >> to be highlighted with the XML
    // mode.
    {regex: /<</, token: "meta", mode: {spec: "xml", end: />>/}}
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
