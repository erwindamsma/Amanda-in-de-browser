editorHasChanges = false;

function newmessage(classname, message, length)
{
    var element = $("<div></div>").addClass(classname).addClass("amandaJSMessage");

    element.css("display","none");

    element.html(""+message+"");
    $("#messageArea").append(element);
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
                    url: "AmandaJs/dropboxproxy.php?u=" + files[0].link,
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
                showInfo("<b>Saved File</b><br>Check your downloads.")
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

window.onParseError = function(type, linenr, columnnr)
{
    showError("<b>Error parsing file!</b><br>At line: "+linenr+" column: " + columnnr+"<br>"+type);
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
            $(data).find("functions function").each(function () {
                var name = $(this).find('name').text();
                var parameter = $(this).find('parameter').text();

                $('#displayTest').append(
                    $('<p />', {
                        text: name
                    }),
                    $('<p />', {
                        text: parameter
                    })
                );
            });
        },
        error: function () {
            $('.displayTest').text('Failed to get feed');
        }
    });
}