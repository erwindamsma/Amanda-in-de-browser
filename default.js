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