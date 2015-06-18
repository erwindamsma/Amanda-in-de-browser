errorcount = 0;
messageQueue = [];
messageLength = 1500;

//Add an DOM element to the message queue
function addToMessageQueue(element)
{
    messageQueue[messageQueue.length] = element;
    if(messageQueue.length == 1) functionNexMessage();
}

//Check if a new message needs to be shown. If so show it.
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

//Create a new message and add it to the message queue.
function newmessage(classname, message)
{
    var element = $("<div></div>").addClass(classname).addClass("amandaJSMessage");

    element.css("display","none");

    element.html(""+message+"");

    addToMessageQueue(element);
}

//Add an error message to the error list.
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

//Clear the error list.
function clearErrors()
{
    $("#errorArea").html("");
    errorcount = 0;
    $("#errorListTabTitle").html("Error List ("+errorcount+")");
}

//Add an info message to the message queue
function showInfo(message)
{
    newmessage("bg-info", "<span class='glyphicon glyphicon-exclamation-sign'></span> "+message, 4000);
}

//Add a waring message to the message queue
function showWarning(message)
{
    newmessage("bg-info", message, 4000);
}

//Method for loading a file into AmandaJS
//Uses Module.ccall to call the compiled C function 'Load'
function AmandaJSLoad(filepath)
{
    clearErrors();

    Module.ccall('Load', // name of C function
        'bool', // return type
        ['string'], // argument types
        [filepath]); // arguments

    initAutoComplete($("#input"));
}

//Create a temporary file in the virutal file system with $fileContent as filedata.
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
    var formData = new FormData($('#uploadform')[0]); //#uploadform is a hidden form.
    $.ajax({
        url: 'AmandaJS/uploadAMA.php',  //Server script to process data
        type: 'POST',
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

//Used to instigate the file browser in the hidden form.
function performClick(elemId) {
    var elem = document.getElementById(elemId);
    if(elem && document.createEvent) {
        var evt = document.createEvent("MouseEvents");
        evt.initEvent("click", true, false);
        elem.dispatchEvent(evt);
    }
}

//Save the functionEditor content to a file, and download it via an hidden iFrame.
function saveEditorToFile(filename)
{
    var jqxhr = $.post( "AmandaJS/saveEditor.php", { editorValue: functionEditor.getValue(), fileName: filename })
        .done(function(data) {
            if(data.lastIndexOf("OK:", 0) === 0)
            {
                uploadedFileUrl = "http://"+data.substring(3);//Without 'OK:' at the start of the string.

                    //Download file via hidden iFrame
                    document.getElementById('downloader').src = uploadedFileUrl;
                    showInfo("<b>Saved File</b><br>Check your downloads.")
                    console.log(uploadedFileUrl);
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

var commandsArray = new Array(); //Command history array
var commandsArrayIndex = 0; //Initialize the current history index on 0;

//Submits the content of the console input to AmandaJS.
//Also checks if up or down arrow where pressed to browse through command history
function submitConsoleInput(event, $value){
    var input = $('#input');

    switch (event.keyCode) {
        case 13: //enter
            document.getElementById("input").value = "";
            Module.print("> " + $value);
            commandsArray.push($value);
            commandsArrayIndex = commandsArray.length;

            switch ($value) {
                //If submitted command is 'time' toggle the button.
                case 'time':
                    toggleTime();
                    break;
                default:
                    interpret($value);
                    break;
            }
            break;

        //Handle command history
        case 38: //arrow up
            if (commandsArrayIndex > 0){
                commandsArrayIndex--;
            }
            input.val(commandsArray[commandsArrayIndex]);
            input.caretToEnd();//Place the text cursor at the end of the line.
            break;
        case 40: //arrow down
            if (commandsArrayIndex < commandsArray.length){
                commandsArrayIndex++;
            }
            input.val(commandsArray[commandsArrayIndex]);
            break;
    }

}

//Clear the functionEditor.
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
        $('#saveFileName').val("untitiled.ama");
        showInfo("<b>Cleared Editor</b>")
    }
    else
    {
        return false;
    }
}

keywords = ["where","where","if","else","True","False","otherwise"]; //Array containing the Amanda keywords

//Checks if a string starts with a Amanda string.
function beginsWithKeyword(str)
{
    for(j = 0 ; j < keywords.length; j++)
    {
        if(str.indexOf(keywords[j]) == 0) return true;
    }
    return false;
}

//Get all the functionnames in the function editor.
function getFunctions()
{
    lines = functionEditor.getValue().split("\n");
    functions = [];
    for(i = 0; i < lines.length; i++)
    {
        if(lines[i].charAt(0) != ' ' && lines[i].indexOf("|") != 0)
        {
            //If the string does not start with a keyword and does contain a equals sign.
            if(!beginsWithKeyword(lines[i]) && lines[i].indexOf("=") != -1)
            {
                lineSplit = lines[i].split("=");
                lineSplit = lineSplit[0].split(" "); //Get content before equals sign, and split on spaces

                exsists = false;
                if(!exsists) { //Check if already added
                    tmp = {};
                    tmp.functionName = lineSplit[0]; //Function name will be the first element in the array split on spaces
                    tmp.arguments = lineSplit.slice(1, lineSplit.length - 1).filter(Boolean); //Arguments are the words after it, .filter to filter out empty entries.
                    functions[functions.length] = tmp;
                }

            }
        }
    }

    return functions;
}

//Initialize jQueryUI autocomplete with functions in the code editor.
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

    $( element ).autocomplete({
        source: availableTags
    });

}

//Sends the 'time' command to AmandaJS and toggles the time button color
function toggleTime(){
    interpret('time');
    $("#toggleTime").toggleClass('active').toggleClass('btn-success');
    if($("#toggleTime").hasClass('active')) showWarning("<span class='glyphicon glyphicon-time'></span> Turned on timing");
    else showWarning("<span class='glyphicon glyphicon-time'></span> Turned off timing");
}

//Calls the Amanda 'Interpret' function with value as argument.
//Used to interpret commands in Amanda
function interpret(value){
    Module.ccall('Interpret', // name of C function
        'void', // return type
        ['string'], // argument types
        [value]); // arguments
}

//Loads and parses XML file from an URL
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

//Display the About content in 'help-modal'
function displayAbout()
{
    //Content of the about modal
    var aboutText = "<p>AmandaJS is the online IDE for the functional programming language Amanda. Amanda and its IDE where first developed by Dick Bruin, teacher at the NHL Hogeschool. AmandaJS is developed by Students from the NHL Hogeschool as a school project. The name AmandaJS refers to the programming language on which AmandaJS is based, JavaScript.</p><p>The purpose of the project which instigated AmandaJS was the research on Emscripten. Emscripten is a cross-compiler which can convert C and C++ to JavaScript making it suitable for the web. More information on Emscripten can be found at https://kripken.github.io/emscripten-site/index.html.</p><p>Erwin Damsma, Sander Jaasma, Jens Vossnack</p><p>June 2015</p>";

    document.getElementById('help-modal').innerHTML = aboutText;
    $('#exampleModalLabel').html("About");
}

//Display the contents of a XML file in the help-modal.
function displayXML (filename){

    //Load the content of the XML file
    xmlDoc = loadXml(filename);
    var modalTitle = "";

    //Check which file has been loaded
    if(filename === 'xml/functions.xml')
    {
        modalTitle = "Functions";
    } else if (filename === 'xml/operators.xml')
    {
        modalTitle = "Operators"
    } else {
        modalTitle = "About"
    }

    //Clear the modal.
    document.getElementById('help-modal').innerHTML = "";
    document.getElementById('exampleModalLabel').innerHTML = "";

    //Get metadata of the file
    _name = xmlDoc.getElementsByTagName("name");
    _parameter = xmlDoc.getElementsByTagName("parameter");
    _inputExample = xmlDoc.getElementsByTagName("inputExample");
    _outputExample = xmlDoc.getElementsByTagName("outputExample");
    _description = xmlDoc.getElementsByTagName("description");

    //Loop through the XML elements and add content to modal.
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
    $('#exampleModalLabel').append(modalTitle); //Set the modal title
}

//Save the functionEditor to dropbox
function saveToDropbox(amatext) {
    //Call dropboxupload.php via an Ajax call
    var request = $.post("dropboxupload.php", {
        editorValue: amatext,
        fileName: $('#saveFileName').val() });

    request.done(function(msg){
        showInfo("Saved file to dropbox.")
    });

    request.fail(function(jqXHR, textStatus) {
        showWarning( "Request failed: " + textStatus );
    });
}

//Called when filename is changed, checks if extension is .ama, if not appends it.
function fileNameChange()
{
    //If the value of saveFileName input doesn't have '.ama' at the end, append it.
    if($('#saveFileName').val().indexOf(".ama") < 0 || $('#saveFileName').val().indexOf(".ama") != $('#saveFileName').val().length - 4)
    {
        $('#saveFileName').val($('#saveFileName').val()+".ama");
    }

}