<?php
    /*REMOVE THIS PART BEFORE GOING LIVE AS IT ENABLES PHP ERRORS
    ################################*/
    error_reporting(-1);
    ini_set('display_errors', 'On');
    /*##############################*/

    include("SDKs/dropbox.php");
?>

<!DOCTYPE html>
<html lang="en" class="fuelux">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <meta name="description" content="">
        <meta name="author" content="Erwin Damsma, Sander Jaasma, Jens Vossnack">
        <title>AmandaOnline</title>

        <!-- Bootstrap core -->
        <link rel="stylesheet" href="bootstrap/bootstrap.css">
        <!-- AmandaJS -->
        <link rel="stylesheet" href="AmandaJS/AmandaJS.css">
        <!-- Codemirror -->
        <link rel="stylesheet" href="codemirror/codemirror.css">
        <link rel="stylesheet" href="codemirror/addon/display/fullscreen.css">
        <link rel="stylesheet" href="codemirror/mode/amanda/amandasyntax.css">
        <!-- jQueryUI -->
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="font-awesome/css/font-awesome.css">
        <!-- Custom -->
        <link rel="stylesheet" href="style.css">

        <!-- Bootstrap core JavaScript
        Place at the end of the document so the pages load faster -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

        <script src="bootstrap/bootstrap.js"></script>
        <!-- The dropbox api -->
        <script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="idcug02opq4uc1h"></script>
        <!-- Codemirror -->
        <script src="codemirror/codemirror.js"></script>
        <script src="codemirror/addon/mode/simple.js"></script>
        <script src="codemirror/mode/amanda/amanda.js"></script>
        <script src="codemirror/addon/display/fullscreen.js"></script>
        <!-- Caret plugin-->
        <script src="Scripts/jquery.caret.js"></script>
        <!-- Custom -->
        <script type='text/javascript' src="Scripts/default.js"></script>

        <!-- Roboto Mono font -->
        <link href='https://fonts.googleapis.com/css?family=Roboto+Mono' rel='stylesheet' type='text/css'>
    </head>
    <body>

        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/amanda">AmandaJS</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                        if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']){
                            echo '<li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                                    ' . $_SESSION['accountInfo']['display_name'] . ' <span class="glyphicon glyphicon-chevron-down" style="font-size: 0.8em"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="?logout">Logout</a></li>
                                </ul>
                            </li>';
                        } else {
                            echo '<li><a href="' . $authorizeUrl . '" style="padding: 14px 15px 23px 15px; height: 14px"><i class="fa fa-dropbox fa-lg"></i></a></li>';
                        }
                        ?>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </nav>

        <div class="container">
            <div id="messageArea"><!-- Messages are shown here --></div>
            <!--Buttons at the top-->
            <div class="row">
                <div class="col-md-12">
                    <div class="btn-group" style="margin-right: 10px">
                        <button class="btn btn-default" onclick="clearEditor()"><span class="glyphicon glyphicon-file"></span></button>
                        <button class="btn btn-default" onclick="performClick('fileUploadInput');" ><span class="glyphicon glyphicon-folder-open"></span></button>
                        <!-- Use loadDropboxFile() for dropbox loading popup-->
                        <button class="btn btn-default" data-toggle="modal" data-target="#saveFileModal" ><span class="glyphicon glyphicon-floppy-disk"></span> </button>
                    </div>
                    <button id="toggleTime" class="btn btn-default" style="margin-right: 10px" onclick="toggleTime()">Timing</button>
                    <div class="btn-group">
                        <button class="btn btn-default" data-toggle="modal" data-target="#helpModal" onclick="displayXML('xml/functions.xml')">Functions</button>
                        <button class="btn btn-default" data-toggle="modal" data-target="#helpModal" onclick="displayXML('xml/operators.xml')">Operators</button>
                        <button class="btn btn-default" data-toggle="modal" data-target="#helpModal" onclick="displayXML('xml/about.xml')">About</button>
                    </div>
                </div>
            </div>

            <!--Help Modal-->
            <div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="exampleModalLabel"></h4>
                        </div>
                        <div class="modal-body">
                            <div id="help-modal">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" id="help-modal-close" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropbox save file Modal-->
            <?php
                if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']){
                    echo '
                        <div class="modal fade" id="saveFileModal" tabindex="-1" role="dialog" aria-labelledby="saveFileModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="saveFileModalLabel">Save to your Dropbox</h4>
                                    </div>
                                    <div class="modal-body">
                                        <label for="saveFileName">Filename:</label>
                                        <input class="form-control" type="text" name="saveFileName" id="saveFileName"/><br>
                                        <div id="mybox"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-default" onclick=" saveToDropbox(functionEditor.getValue());" >Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';
                }

            ?>

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
                            <li role="presentation"><a id="errorListTabTitle" href="#errorlist" role="tab" data-toggle="tab">Error List (0)</a></li>
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
                                <div id="errorArea"><!-- Messages are shown here --></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

      <!--      <script>
                $('#helpModal').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget)
                    var category = button.data('whatever')
                    var cat = category.toLowerCase();
                    var modal = $(this)
                    modal.find('.modal-title').text(category)
                    modal.find('.modal-body div .displayTest').text(loadXml(cat))
                })
            </script> -->

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


        </div>

        <!-- All hidden input -->
        <div style="display: none;">
            <!-- This iframe is used to download files -->
            <iframe id="downloader" style='display:none;'></iframe>

            <form id="uploadform" style="display: none;">
                <label for="uploadFile">Browse File:</label> <input id="fileUploadInput" name="uploadFile" type="file" />
                <input type="button" value="Load File" name="submit" onclick="uploadAndLoadFile();"/>
            </form>
            <script>
                $(document).ready(function(){
                    $("#fileUploadInput").change(function()
                    {
                        uploadAndLoadFile();
                    });
                });
            </script>
        </div>
    </body>
</html>