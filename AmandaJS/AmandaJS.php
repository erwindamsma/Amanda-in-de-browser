<link href="AmandaJS/AmandaJS.css" rel="stylesheet">

<div class="spinner" id='spinner'></div>
<div class="emscripten" id="status">Downloading...</div>

<!--<span id='controls'>
  <span><input type="checkbox" id="resize">Resize canvas</span>
  <span><input type="checkbox" id="pointerLock" checked>Lock/hide mouse pointer &nbsp;&nbsp;&nbsp;</span>
  <span><input type="button" value="Fullscreen" onclick="Module.requestFullScreen(document.getElementById('pointerLock').checked,
                                                                            document.getElementById('resize').checked)">
  </span>
</span>-->

<!--    <div class="emscripten">
      <progress value="0" max="100" id="progress" hidden=1></progress>
    </div>-->


    <!--<div class="emscripten_border">
      <canvas class="emscripten" id="canvas" oncontextmenu="event.preventDefault()"></canvas>
    </div>-->

<div class="row">
    <div class="col-md-12">
        <textarea id="functions" class="form-control" rows="12"></textarea>
    </div>
</div>

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
                            <input type="text" class="form-control" id="input">
                            <textarea id="output" class="form-control" rows="8" readonly></textarea>
                        </div>
                        <div class="col-md-2">
                            <input class="btn btn-default consoleButtons" type="button" value="Stop">
                            <input class="btn btn-default consoleButtons" type="button" value="Clear">
                        </div>
                    </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="errorlist"></div>
                <div role="tabpanel" class="tab-pane" id="graphic"></div>
            </div>
        </div>
    </div>
</div>







<script type='text/javascript'>
  var statusElement = document.getElementById('status');
  //var progressElement = document.getElementById('progress');
  var spinnerElement = document.getElementById('spinner');

  var Module = {
    preRun: [],
    postRun: [],
    print: (function() {
      var element = document.getElementById('output');
      if (element) element.value = ''; // clear browser cache
      return function(text) {
        if (arguments.length > 1) text = Array.prototype.slice.call(arguments).join(' ');
        // These replacements are necessary if you render to raw HTML
        //text = text.replace(/&/g, "&amp;");
        //text = text.replace(/</g, "&lt;");
        //text = text.replace(/>/g, "&gt;");
        //text = text.replace('\n', '<br>', 'g');
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
    //canvas: (function() {
      //var canvas = document.getElementById('canvas');

      // As a default initial behavior, pop up an alert when webgl context is lost. To make your
      // application robust, you may want to override this behavior before shipping!
      // See http://www.khronos.org/registry/webgl/specs/latest/1.0/#5.15.2
      //canvas.addEventListener("webglcontextlost", function(e) { alert('WebGL context lost. You will need to reload the page.'); e.preventDefault(); }, false);

      //return canvas;
    //})(),
    setStatus: function(text) {
      if (!Module.setStatus.last) Module.setStatus.last = { time: Date.now(), text: '' };
      if (text === Module.setStatus.text) return;
      var m = text.match(/([^(]+)\((\d+(\.\d+)?)\/(\d+)\)/);
      var now = Date.now();
      if (m && now - Date.now() < 30) return; // if this is a progress update, skip it if too soon
      if (m) {
        text = m[1];
        //progressElement.value = parseInt(m[2])*100;
        //progressElement.max = parseInt(m[4])*100;
        //progressElement.hidden = false;
        spinnerElement.hidden = false;
      } else {
        //progressElement.value = null;
        //progressElement.max = null;
        //progressElement.hidden = true;
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
<script async type="text/javascript" src="AmandaJS/AmandaJS.js"></script>