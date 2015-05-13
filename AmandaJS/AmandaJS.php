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
            <button class="btn btn-default" onclick="clearEditor()"><span class="glyphicon glyphicon-file"></span></button>
            <button class="btn btn-default" onclick="loadDropboxFile();"><span class="glyphicon glyphicon-folder-open"></span></button>
            <iframe id="downloader" style='display:none;'></iframe><!-- This iframe is used to download files -->
            <button class="btn btn-default" onclick="saveEditorToFile()"><span class="glyphicon glyphicon-floppy-disk"></span> </button>
        </div>
        <div class="btn-group" style="margin-right: 10px">
            <button class="btn btn-default" disabled><span class="glyphicon glyphicon-arrow-left"></span></button>
            <button class="btn btn-default" disabled><span class="glyphicon glyphicon-arrow-right"></span></button>
        </div>
        <button id="toggleTime" class="btn btn-default" style="margin-right: 10px" onclick="toggleTime()">Timing</button>
        <div class="btn-group">
            <button class="btn btn-default" disabled>Functions</button>
            <button class="btn btn-default" disabled>Operators</button>
            <button class="btn btn-default" disabled>About</button>
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
                <li role="presentation"><a href="#errorlist" role="tab" data-toggle="tab">Error list</a></li>
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
                            <input class="btn btn-default consoleButtons" type="button" value="Stop" disabled>
                            <input class="btn btn-default consoleButtons" type="button" value="Clear" onclick="$('#output').val('');">
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
    editorHasChanges = false;

    function loadTempFile($fileContent)
    {
        filepath = "/tmp";
        filename = "tmp.ama";

        fileData = $fileContent;

        //Here we create a file in the Emscripten virutal file system.
        if(FS.findObject(filepath + "/" + filename) != null) FS.unlink(filepath + "/" + filename);//Remove the tmp file
        FS.createDataFile(filepath, filename, fileData, true, true, true); //Create the tmp.ama file

        //Load '/tmp/tmp.ama' in AmandaJs by calling the Load function
        Module.ccall('Load', // name of C function
            'bool', // return type
            ['string'], // argument types
            [filepath + "/" + filename]); // arguments
    }


    function loadDropboxFile()
    {
        options = {

            // Required. Called when a user selects an item in the Chooser.
            success: function(files) {
                alert("Here's the file link: " + files[0].link)
                $.ajax({
                    url: "AmandaJs/dropboxproxy.php?u="+files[0].link,
                    type: 'GET',
                    beforeSend: function (xhr) {
                        xhr.overrideMimeType("text/plain; charset=x-user-defined");
                    },
                    success: function( data ) {
                        functionEditor.setValue(data);
                        Module['FS_createDataFile']("/tmp", "uploaded.ama", data, true, true);
                        Module.ccall('Load', // name of C function
                            'bool', // return type
                            ['string'], // argument types
                            ["/tmp/uploaded.ama"]); // arguments
                    }
                });

            },

            // Optional. Called when the user closes the dialog without selecting a file
            // and does not include any parameters.
            cancel: function() {

            },

            // Optional. "preview" (default) is a preview link to the document for sharing,
            // "direct" is an expiring link to download the contents of the file. For more
            // information about link types, see Link types below.
            linkType: "direct", // or "direct"

            // Optional. A value of false (default) limits selection to a single file, while
            // true enables multiple file selection.
            multiselect: false, // or true

            // Optional. This is a list of file extensions. If specified, the user will
            // only be able to select files with these extensions. You may also specify
            // file types, such as "video" or "images" in the list. For more information,
            // see File types below. By default, all extensions are allowed.
            extensions: ['.txt', '.ama']
        };

        Dropbox.choose(options);

    }

    function saveEditorToFile()
    {
        var jqxhr = $.post( "AmandaJs/saveEditor.php", { editorValue: functionEditor.getValue() })
            .done(function(data) {
                if(data.lastIndexOf("OK:", 0) === 0)
                {
                    uploadedFileUrl = "http://"+data.substring(3);

                    console.log(uploadedFileUrl);
                    //Download file via hidden iFrame
                    document.getElementById('downloader').src = uploadedFileUrl;


                    //We can save to dropbox when we move to a server
                   /* var options = {
                        success: function () {
                            // Indicate to the user that the files have been saved.
                            alert("Success! Files saved to your Dropbox.");
                        }
                    };
                    console.log(uploadedFileUrl);
                    Dropbox.save(uploadedFileUrl, "debug.ama", options);*/

                }
                else
                {
                    alert("Something went wrong saving the file to our servers..");

                }
            })
            .fail(function() {
                alert("Something went wrong saving the file to our servers..");
            });


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

    function clearEditor()
    {
        conf = !editorHasChanges;

        if(editorHasChanges)
        {
            conf = confirm("Unsaved changes will be lost, are you sure?");
        }


        if(conf)
        {
            functionEditor.setValue("");
            editorHasChanges = false;
        }
        else
        {
            return false;
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

    //We need a sleep function, dropbox.save can only be called from a click event, and we need to wait for the upload to complete.
    function sleep(milliseconds) {
        var start = new Date().getTime();
        for (var i = 0; i < 1e7; i++) {
            if ((new Date().getTime() - start) > milliseconds){
                break;
            }
        }
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

<!-- The dropbox api -->
<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="idcug02opq4uc1h"></script>

</script>

<script>
    var functionEditor = CodeMirror.fromTextArea(document.getElementById("functions"), {
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
    functionEditor.on("blur",function(instance){loadTempFile(instance.getValue());});
    functionEditor.on("change",function(instance){editorHasChanges = true;});
</script>

<script async type="text/javascript" src="AmandaJS/AmandaJS.js"></script>