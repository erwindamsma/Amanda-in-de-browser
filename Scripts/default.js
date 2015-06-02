errorcount = 0;
messageQueue = [];
messageLength = 1500;

window.onParseError = function(type, linenr, columnnr)
{
    if(linenr != -1 && columnnr != -1)
        showError("<b>Syntax error!</b><br>At line: "+linenr+" column: " + columnnr+"<br>"+type);
    else
        showError("<b>Syntax error!</b><br>"+type);
}

function addToMessageQueue(element)
{
    messageQueue[messageQueue.length] = element;
    if(messageQueue.length == 1) functionNexMessage();
}

function functionNexMessage(){
    if(messageQueue.length == 0) return;

    $("#messageArea").prepend(messageQueue[0]);
    $(messageQueue[0]).fadeIn();
    $(messageQueue[0]).animate({bottom:'0px'},1000);

    messageQueue[0].messageTimeout = setTimeout(function () {
        $.when($(messageQueue[0]).fadeOut(500))
            .done(function() {
                $(messageQueue[0]).remove();
                messageQueue.splice(0, 1);
                functionNexMessage();

            });
    }, messageLength);



}

function newmessage(classname, message)
{
    var element = $("<div></div>").addClass(classname).addClass("amandaJSMessage");

    element.css("display","none");

    element.html(""+message+"");

    addToMessageQueue(element);

}


function showError(message)
{
    var element = $("<div></div>").addClass("bg-danger").addClass("amandaJSError");

    var currentdate = new Date();


    element.css("display","none");

    time=('0'  + currentdate.getMinutes()).slice(-2)+':'+('0' + currentdate.getSeconds()).slice(-2);

    element.html(message+"<br>"+currentdate.getHours() + ":" + time);
    $("#errorArea").prepend(element);
    $(element).fadeIn();

    errorcount++;

    $("#errorListTabTitle").html("Error List ("+errorcount+")");
}

function clearErrors()
{
    $("#errorArea").html("");
    errorcount = 0;
    $("#errorListTabTitle").html("Error List ("+errorcount+")");
}

function showInfo(message)
{
    newmessage("bg-info", "<span class='glyphicon glyphicon-exclamation-sign'></span> "+message, 4000);
}

function showWarning(message)
{
    newmessage("bg-info", message, 4000);
}

function fadeMessageAfterTimeout(element)
{


}

function AmandaJSLoad(filepath)
{
    clearErrors();

    Module.ccall('Load', // name of C function
        'bool', // return type
        ['string'], // argument types
        [filepath]); // arguments

    initAutoComplete($("#input"));
}

function loadTempFile($fileContent)
{
    filepath = "/tmp";
    filename = "tmp.ama";

    fileData = $fileContent;

    //Here we create a file in the Emscripten virutal file system.
    if(FS.findObject(filepath + "/" + filename) != null) FS.unlink(filepath + "/" + filename);//Remove the tmp file
    FS.createDataFile(filepath, filename, fileData, true, true, true); //Create the tmp.ama file

    AmandaJSLoad(filepath + "/" + filename);
}

//Saves the file selected in the open file modal to the server, and loads this file into the codemirror editor
function uploadAndLoadFile()
{
    var formData = new FormData($('#uploadform')[0]);
    $.ajax({
        url: 'AmandaJS/uploadAMA.php',  //Server script to process data
        type: 'POST',
        /*xhr: function() {  // Custom XMLHttpRequest
         var myXhr = $.ajaxSettings.xhr();
         if(myXhr.upload){ // Check if upload property exists
         myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
         }
         return myXhr;
         },*/
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false

    }).done(function(data)
    {
        if(data.indexOf("OK:") == 0)
        {
            //Fileupload succesfull
            fileContent = data.substring(3);//Without 'OK:' at the start of the string.
            loadTempFile(fileContent);
            functionEditor.setValue(fileContent);
            $('#loadFileModal').modal('hide')
        }
        else
        {
            showInfo("Something went wrong uploading your file: " );
        }
    });
}

function performClick(elemId) {
    var elem = document.getElementById(elemId);
    if(elem && document.createEvent) {
        var evt = document.createEvent("MouseEvents");
        evt.initEvent("click", true, false);
        elem.dispatchEvent(evt);
    }
}

function loadDropboxFile()
{
    conf = true;

    if(functionEditor.getValue().length > 0)
    {
        conf = confirm("Unsaved changes will be lost, are you sure?");
    }
    if(conf) {
        options = {

            // Required. Called when a user selects an item in the Chooser.
            success: function (files) {
                showInfo("<b>Started Loading:</b><br>" + files[0].link);
                $.ajax({
                    url: "AmandaJS/dropboxproxy.php?u=" + files[0].link,
                    type: 'GET',
                    beforeSend: function (xhr) {
                        xhr.overrideMimeType("text/plain; charset=x-user-defined");
                    },
                    success: function (data) {

                        functionEditor.setValue(data);//Set the editor content


                        if (FS.findObject("/tmp/uploaded.ama") != null) FS.unlink("/tmp/uploaded.ama");//Remove the tmp file
                        Module['FS_createDataFile']("/tmp", "uploaded.ama", data, true, true);

                        AmandaJSLoad("/tmp/uploaded.ama");

                        showInfo("<b>Finished Loading:</b><br>" + files[0].link);
                    }
                });
            },
            cancel: function () {
            },
            // Optional. "preview" (default) is a preview link to the document for sharing,
            // "direct" is an expiring link to download the contents of the file. For more
            // information about link types, see Link types below.
            linkType: "direct", // or "direct"
            multiselect: false, // or true
            extensions: ['.txt', '.ama']
        };
        Dropbox.choose(options);
    }
}

//saveLocal bool, filename string
function saveEditorToFile(saveLocal, filename)
{
    var jqxhr = $.post( "AmandaJS/saveEditor.php", { editorValue: functionEditor.getValue(), fileName: filename })
        .done(function(data) {
            if(data.lastIndexOf("OK:", 0) === 0)
            {
                uploadedFileUrl = "http://"+data.substring(3);//Without 'OK:' at the start of the string.

                if(saveLocal) {
                    //Download file via hidden iFrame
                    document.getElementById('downloader').src = uploadedFileUrl;
                    showInfo("<b>Saved File</b><br>Check your downloads.")
                    console.log(uploadedFileUrl);
                }
                else
                {
                    var options = {
                        success: function () {
                            // Indicate to the user that the files have been saved.
                            showInfo("Successfully saved files to your Dropbox.");
                        }
                    };
                    //console.log(uploadedFileUrl);
                    Dropbox.save(uploadedFileUrl, filename+".ama", options);
                }

            }
            else
            {
                showError("Something went wrong saving the file to our servers..");
            }
        })
        .fail(function() {
            showError("Something went wrong saving the file to our servers..");
        });
}

var commandsArray = new Array();
var commandsArrayIndex = 0;
function submitConsoleInput($value){
    var input = $('#input');

    switch (event.keyCode) {
        case 13: //enter
            document.getElementById("input").value = "";
            Module.print("> " + $value);
            commandsArray.push($value);
            commandsArrayIndex = commandsArray.length;

            switch ($value) {
                case 'time':
                    toggleTime();
                    break;
                default:
                    interpret($value);
                    break;
            }
            break;
        case 38: //arrow up
            if (commandsArrayIndex > 0){
                commandsArrayIndex--;
            }
            input.val(commandsArray[commandsArrayIndex]);
            input.caretToEnd();
            break;
        case 40: //arrow down
            if (commandsArrayIndex < commandsArray.length){
                commandsArrayIndex++;
            }
            input.val(commandsArray[commandsArrayIndex]);
            break;
    }

}

function clearEditor()
{
    conf = true;

    if(functionEditor.getValue().length > 0)
    {
        conf = confirm("Unsaved changes will be lost, are you sure?");
    }

    if(conf)
    {
        functionEditor.setValue("");
        showInfo("<b>Cleared Editor</b>")
    }
    else
    {
        return false;
    }
}

keywords = ["where","where","if","else","True","False","otherwise"];



function beginsWithKeyword(str)
{
    for(j = 0 ; j < keywords.length; j++)
    {
        if(str.indexOf(keywords[j]) == 0) return true;
    }
    return false;
}

function getFunctions()
{
    lines = functionEditor.getValue().split("\n");
    functions = [];
    for(i = 0; i < lines.length; i++)
    {
        if(lines[i].charAt(0) != ' ' && lines[i].indexOf("|") != 0)
        {

            if(!beginsWithKeyword(lines[i]) && lines[i].indexOf("=") != -1)
            {
                lineSplit = lines[i].split("=");
                lineSplit = lineSplit[0].split(" ");
                //Check if already added
                exsists = false;
                /*for(k = 0; k < functions.length; k++)
                {
                    if(functions[k].functionName == lineSplit[0]){
                        exsists = true;
                        break;
                    }
                }*/
                if(!exsists) {
                    tmp = {};
                    tmp.functionName = lineSplit[0];
                    tmp.arguments = lineSplit.slice(1, lineSplit.length - 1).filter(Boolean);
                    functions[functions.length] = tmp;
                }

            }
        }
    }


    return functions;
}

function initAutoComplete(element)
{
    functionObjects = getFunctions();
    availableTags = [];
    for(q = 0; q < functionObjects.length; q++)
    {
        functionString = functionObjects[q].functionName;
        for(j = 0; j < functionObjects[q].arguments.length;j++){
            functionString += " " + functionObjects[q].arguments[j];
        }

        tmpObj = {
            label:  functionString,
            value:    functionObjects[q].functionName + " "
        };

        availableTags[q] = tmpObj;
    }

    //console.log(availableTags);

    $( element ).autocomplete({
        source: availableTags
    });

}

function toggleTime(){
    interpret('time');
    $("#toggleTime").toggleClass('active').toggleClass('btn-success');
    if($("#toggleTime").hasClass('active')) showWarning("<span class='glyphicon glyphicon-time'></span> Turned on timing");
    else showWarning("<span class='glyphicon glyphicon-time'></span> Turned off timing");
}

function interpret(value){
    Module.ccall('Interpret', // name of C function
        'void', // return type
        ['string'], // argument types
        [value]); // arguments
}



function loadXml(fileName) {
    if (window.XMLHttpRequest)
    {
        xhttp = new XMLHttpRequest();
    }
    else // code for IE5 and IE6
    {
        xhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xhttp.open("GET",fileName, false);
    xhttp.send();
    return xhttp.responseXML;
}


function displayXML (filename){
    //xmlDoc = loadXml("functions.xml");
    xmlDoc = loadXml(filename);
    var modalTitle = ""
    if(filename === 'xml/functions.xml')
    {
        modalTitle = "Functions";
    } else if (filename === 'xml/operators.xml')
    {
        modalTitle = "Operators"
    } else {
        modalTitle = "About"
    }
    document.getElementById('help-modal').innerHTML = "";
    document.getElementById('exampleModalLabel').innerHTML = "";
    _name = xmlDoc.getElementsByTagName("name");
    _parameter = xmlDoc.getElementsByTagName("parameter");
    _inputExample = xmlDoc.getElementsByTagName("inputExample");
    _outputExample = xmlDoc.getElementsByTagName("outputExample");
    _description = xmlDoc.getElementsByTagName("description");
    for (i = 0; i < _name.length; i++)
    {
        var element = $('<tr></tr>').addClass("row-help-modal");

        $(element).append('<td class="td-helpContent name">'+_name[i].childNodes[0].nodeValue+'</td>');
        $(element).append('<td class="td-helpContent parameter">'+_parameter[i].childNodes[0].nodeValue+'</td>');
        $(element).append('<td class="td-helpContent inputExample">'+_inputExample[i].childNodes[0].nodeValue+'</td>');
        $(element).append('<td class="td-helpContent outputExample">'+_outputExample[i].childNodes[0].nodeValue+'</td>');
        $(element).append('<td class="td-helpContent description">'+_description[i].childNodes[0].nodeValue+'</td>');

        $('#help-modal').append(element);
    }
    $('#exampleModalLabel').append(modalTitle);
}

function saveToDropbox(amatext) {
    var request = $.post("dropboxupload.php", {
        editorValue: amatext,
        fileName: $('#saveFileName').val() });

    request.done(function(msg){
        $("#mybox").html(msg);
    });

    request.fail(function(jqXHR, textStatus) {
        alert( "Request failed: " + textStatus );
    });
}