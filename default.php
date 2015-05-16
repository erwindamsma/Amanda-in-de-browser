<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../../favicon.ico">
        <title>AmandaOnline</title>

        <!--###########################
        #########    CSS      #########
        ############################-->
        <!-- Bootstrap core -->
        <link href="bootstrap/bootstrap.css" rel="stylesheet">
        <!-- AmandaJS -->
        <link href="AmandaJS/AmandaJS.css" rel="stylesheet">
        <!-- Codemirror -->
        <link rel="stylesheet" href="codemirror/codemirror.css">
        <link rel="stylesheet" href="codemirror/addon/display/fullscreen.css">
        <!-- Custom -->
        <link href="style.css" rel="stylesheet">

        <!--###########################
        ######### JavaScript  #########
        ############################-->
        <!-- Bootstrap core JavaScript
        Place at the end of the document so the pages load faster -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="bootstrap/bootstrap.js"></script>
        <!-- Dropbox -->
        <script src="https://www.dropbox.com/static/api/dropbox-datastores-1.2-latest.js" type="text/javascript"></script>
        <!-- The dropbox api -->
        <script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="idcug02opq4uc1h"></script>
        <!-- Codemirror -->
        <script src="codemirror/codemirror.js"></script>
        <!--<link rel="stylesheet" href="codemirror/theme/night.css">-->
        <script src="codemirror/addon/mode/simple.js"></script>
        <script src="codemirror/mode/amanda/amanda.js"></script>
        <script src="codemirror/addon/display/fullscreen.js"></script>
        <!-- Caret plugin-->
        <script src="Scripts/jquery.caret.js"></script>
        <!-- Custom -->
        <script type='text/javascript' src="Scripts/default.js"></script>
    </head>
    <body>

        <?php
            include "Shared/navbar.php";
        ?>

        <div class="container">
            <div id="messageArea"></div>
            <!--Buttons at the top-->
            <div class="row">
                <div class="col-md-12">


                    <div class="btn-group" style="margin-right: 10px">
                        <button class="btn btn-default" onclick="clearEditor()"><span class="glyphicon glyphicon-file"></span></button>
                        <button class="btn btn-default" onclick="loadDropboxFile();"><span class="glyphicon glyphicon-folder-open"></span></button>
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
                                        <input type="text" class="form-control" id="input" disabled onkeyup="submitConsoleInput(this.value)">
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

            <!--Amanda js code which emscripten normally puts in this page itself-->
            <script type='text/javascript' src="Scripts/AmandaJSpage.js"></script>

            <!-- Codemirror (for the functions textarea) -->
            <script>
                var functionEditor = CodeMirror.fromTextArea(document.getElementById("functions"), {
                    lineNumbers: true,
                    mode: "amandamode",
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

            <!-- Emscripten compiled amanda code -->
            <script async type="text/javascript" src="AmandaJS/AmandaJS.js"></script>

            <!-- This iframe is used to download files -->
            <iframe id="downloader" style='display:none;'></iframe>
        </div>

    </body>
</html>