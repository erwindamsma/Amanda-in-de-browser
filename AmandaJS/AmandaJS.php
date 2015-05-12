<link href="AmandaJS/AmandaJS.css" rel="stylesheet">

<link rel="stylesheet" href="codemirror/codemirror.css">
<link rel="stylesheet" href="codemirror/addon/display/fullscreen.css">
<script src="codemirror/codemirror.js"></script>
<!--<link rel="stylesheet" href="codemirror/theme/night.css">-->
<!--<script src="codemirror/mode/amanda/amanda.js"></script>-->
<script src="codemirror/addon/display/fullscreen.js"></script>

<div class="spinner" id='spinner'></div>
<div class="emscripten" id="status">Downloading...</div>

<!--Buttons at the top-->
<div class="row">
    <div class="col-md-12">
        <div class="btn-group" style="margin-right: 10px">
            <button class="btn btn-default"><span class="glyphicon glyphicon-file"></span></button>
            <button class="btn btn-default"><span class="glyphicon glyphicon-folder-open"></span></button>
            <button class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk"></span> </button>
        </div>
        <div class="btn-group" style="margin-right: 10px">
            <button class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span></button>
            <button class="btn btn-default"><span class="glyphicon glyphicon-arrow-right"></span></button>
        </div>
        <button id="toggleTime" class="btn btn-default" style="margin-right: 10px" onclick="toggleTime()">Timing</button>
        <div class="btn-group">
            <button class="btn btn-default">Functions</button>
            <button class="btn btn-default">Operators</button>
            <button class="btn btn-default">About</button>
        </div>
    </div>
</div>

<!--Functions textarea-->
<div class="row">
    <div class="col-md-12">
        <textarea id="functions" class="form-control" rows="12" onblur="loadTempFile();"></textarea>
    </div>
</div>

<!--Console, Error list and Graphic-->
<div class="row">
    <div class="col-md-12">
        <div role="tabpanel" id="amandaTabs">
            <!-- Tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#console" role="tab" data-toggle="tab">Console</a></li>
                <li role="presentation"><a href="#errorlist" role="tab" data-toggle="tab">Error ist</a></li>
                <li role="presentation"><a href="#graphic" role="tab" data-toggle="tab">Graphic</a></li>
            </ul>
            <!-- Panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="console">
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" class="form-control" id="input" onkeypress="submitConsoleInput(this.value)">
                            <textarea id="output" class="form-control" rows="8" readonly></textarea>
                        </div>
                        <div class="col-md-2">
                            <input class="btn btn-default consoleButtons" type="button" value="Stop">
                            <input class="btn btn-default consoleButtons" type="button" value="Clear">
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="errorlist">

                </div>
                <div role="tabpanel" class="tab-pane" id="graphic">
                    <h2>Graphic is not (yet) implemented.</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<script type='text/javascript'>
    function loadTempFile($fileContent)
    {
        filepath = "/tmp";
        filename = "tmp.ama";

        fileData = $fileContent;

        if(FS.findObject(filepath + "/" + filename) != null) FS.unlink(filepath + "/" + filename);//Remove the tmp file
        FS.createDataFile(filepath, filename, fileData, true, true, true); //Create the tmp.ama file

        //Load /tmp/tmp.ama in AmandaJs
        Module.ccall('Load', // name of C function
            'bool', // return type
            ['string'], // argument types
            [filepath + "/" + filename]); // arguments
    }

    function loadPersistentFile($url)
    {

    }

    function submitConsoleInput($value){
        if (event.keyCode == 13) {
            document.getElementById("input").value = "";
            Module.print("> "+$value);

            switch ($value){
                case 'time':
                    toggleTime();
                    break;
                default:
                    interpret($value);
                    break;
            }
            
        }
    }

    function toggleTime(){
        interpret('time');
        $("#toggleTime").toggleClass('active').toggleClass('btn-success');
    }

    function interpret(value){
        Module.ccall('Interpret', // name of C function
            'void', // return type
            ['string'], // argument types
            [value]); // arguments
    }
</script>

<script type='text/javascript'>
  var statusElement = document.getElementById('status');
  var spinnerElement = document.getElementById('spinner');

  var Module = {
    preRun: [],
    postRun: [],
    print: (function() {
      var element = document.getElementById('output');
      if (element) element.value = ''; // clear browser cache
      return function(text) {
        if (arguments.length > 1) text = Array.prototype.slice.call(arguments).join(' ');
        console.log(text);
        if (element) {
          element.value += text + "\n";
          element.scrollTop = element.scrollHeight; // focus on bottom
        }
      };
    })(),
    printErr: function(text) {
      if (arguments.length > 1) text = Array.prototype.slice.call(arguments).join(' ');
      if (0) { // XXX disabled for safety typeof dump == 'function') {
        dump(text + '\n'); // fast, straight to the real console
      } else {
        console.error(text);
      }
    },
    setStatus: function(text) {
      if (!Module.setStatus.last) Module.setStatus.last = { time: Date.now(), text: '' };
      if (text === Module.setStatus.text) return;
      var m = text.match(/([^(]+)\((\d+(\.\d+)?)\/(\d+)\)/);
      var now = Date.now();
      if (m && now - Date.now() < 30) return; // if this is a progress update, skip it if too soon
      if (m) {
        text = m[1];
        spinnerElement.hidden = false;
      } else {
        if (!text) spinnerElement.style.display = 'none';
      }
      statusElement.innerHTML = text;
    },
    totalDependencies: 0,
    monitorRunDependencies: function(left) {
      this.totalDependencies = Math.max(this.totalDependencies, left);
      Module.setStatus(left ? 'Preparing... (' + (this.totalDependencies-left) + '/' + this.totalDependencies + ')' : 'All downloads complete.');
    }
  };
  Module.setStatus('Downloading...');
  window.onerror = function(event) {
    // TODO: do not warn on ok events like simulating an infinite loop or exitStatus
    Module.setStatus('Exception thrown, see JavaScript console');
    spinnerElement.style.display = 'none';
    Module.setStatus = function(text) {
      if (text) Module.printErr('[post-exception status] ' + text);
    };
  };
</script>

<script>
    var editor = CodeMirror.fromTextArea(document.getElementById("functions"), {
        lineNumbers: true,
        theme: "night",
        extraKeys: {
            "F11": function(cm) {
                cm.setOption("fullScreen", !cm.getOption("fullScreen"));
            },
            "Esc": function(cm) {
                if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
            }
        }

    });

    //Add onBlur event to the editor, when lost focus load the temp file.
    editor.on("blur",function(instance){loadTempFile(instance.getValue());});
</script>

<script async type="text/javascript" src="AmandaJS/AmandaJS.js"></script>