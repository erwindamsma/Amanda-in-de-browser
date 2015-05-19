editorHasChanges = false;

window.onParseError = function(type, linenr, columnnr)
{
    if(linenr != -1 && columnnr != -1)
        showError("<b>Error parsing file!</b><br>At line: "+linenr+" column: " + columnnr+"<br>"+type);
    else
        showError("<b>Error parsing file!</b><br>"+type);
}

function newmessage(classname, message, length)
{
    var element = $("<div></div>").addClass(classname).addClass("amandaJSMessage");

    element.css("display","none");

    element.html(""+message+"");
    $("#messageArea").prepend(element);
    $(element).fadeIn();

    element.messageTimeout = setTimeout(function() {
        errorTimer = fadeMessageAfterTimeout(element);
    }, length);
}


function showError(message)
{
    newmessage("bg-danger", message, 4000);
}

function showInfo(message)
{
    newmessage("bg-info", "<span class='glyphicon glyphicon-exclamation-sign'></span> "+message, 4000);
}

function showWarning(message)
{
    newmessage("bg-warning", message, 4000);
}

function fadeMessageAfterTimeout(element)
{
    $.when($(element).fadeOut(500))
        .done(function() {
            element.remove();
        });

}

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

    initAutoComplete($("#input"));
}

function loadDropboxFile()
{
    conf = !editorHasChanges;

    if(editorHasChanges)
    {
        conf = confirm("Unsaved changes will be lost, are you sure?");
    }
    if(conf) {
        options = {

            // Required. Called when a user selects an item in the Chooser.
            success: function (files) {
                showInfo("<b>Loading:</b><br>" + files[0].link);
                $.ajax({
                    url: "AmandaJS/dropboxproxy.php?u=" + files[0].link,
                    type: 'GET',
                    beforeSend: function (xhr) {
                        xhr.overrideMimeType("text/plain; charset=x-user-defined");
                    },
                    success: function (data) {

                        functionEditor.setValue(data);//Set the editor content
                        editorHasChanges = false;

                        if (FS.findObject("/tmp/uploaded.ama") != null) FS.unlink("/tmp/uploaded.ama");//Remove the tmp file
                        Module['FS_createDataFile']("/tmp", "uploaded.ama", data, true, true);
                        Module.ccall('Load', // name of C function
                            'bool', // return type
                            ['string'], // argument types
                            ["/tmp/uploaded.ama"]); // arguments

                        initAutoComplete($("#input"));

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
    if($("#ui-id-1").css("display") == "block")//If the autocomplete dropdown is visible, dont capture input.
    {
        //Do nothing
    }
    else
    {
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
        if(lines[i].charAt(0) != ' ')
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

    console.log(availableTags);

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



function loadXml(xmlFileName) {
    var str1 = "xml/";
    var str2 = xmlFileName;
    var str3 = ".xml";
    var urlString = str1.concat(str2,str3);
    console.log(urlString);
    $.ajax({
        url: urlString,
        dataType: 'xml',
        success: function (data) {
            $(' .cus-container-content-help-modal').html("");
            $(data).find("functions function").each(
                function () {
                    var name = $(this).find('name').text();
                    var parameter = $(this).find('parameter').text();
                    var inputExample = $(this).find('inputExample').text();
                    var outputExample = $(this).find('outputExample').text();
                    var description = $(this).find('description').text();

                    $(' #displayTest').append($('<div>'));
                    $(' .container-cus-help-modal').append($('<p></p>').text(name));
                    $(' .container-cus-help-modal').append($('<p></p>').text(parameter));
                    $(' .container-cus-help-modal').append($('<p></p>').text(inputExample));
                    $(' .container-cus-help-modal').append($('<p></p>').text(outputExample));
                    $(' .container-cus-help-modal').append($('<p></p>').text(description));
                    $( name).append($('</div>'));

                    $(' #displayTest div').addClass('container-cus-help-modal');
                    $('<div.container-cus-help-modal>').attr('id', name);

                });
        },
        error: function () {
            $(' #displayTest').text('Failed to get feed');
        }
    });
}