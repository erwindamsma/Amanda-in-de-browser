/**
 * Created by Jens on 3-5-2015.
 */
var currentTTY = null;

// Call C from JavaScript
var amandajs_interpret = Module.cwrap('Interpret', // name of C function
    'void', // return type
    ['array']); // argument types


function onKeyDownConsoleInput(event)
{
    //If keycode == enter

    if(event.keyCode == 13)
    {
        amandajs_interpret(event.target.value);
        //currentTTY.inputBuffer = event.target.value + "\n";
        //currentTTY.hasNewLine = true;
        console.log("ITEST");
    }


}


/*
//Add the last character typed to the buffer
function addToBuffer(input)
{
    currentTTY.inputBuffer += input;
    if(input == "\n") sendBuffer();
}

//Send the buffer to the tty.
function sendBuffer()
{
    currentTTY.hasNewLine = true;
    //Call the input function of emscripten with inputbuffer as input
    inputBuffer = "";
}*/
