<?php

require_once __DIR__ . '/../components/header.php';

?>
  <h1 class="text-2xl">Create new photobomb</h1>
  <div class="sf-create">
    <div class="camera-wrap">
      <div class="camera-holder">
        <video id="sf-video">Video stream not available.</video>
        <button id="sf-capture"></button>
      </div>
    </div>
    <canvas id="sf-draw">
    </canvas>
    <div class="output">
      <img id="photo" alt="The screen capture will appear in this box.">
    </div>
  </div>
<?php

require_once __DIR__ . '/../components/footer.php';
