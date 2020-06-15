<?php

require_once __DIR__ . '/../components/header.php';

?>
  <h1 class="text-2xl">Create new photobomb</h1>
  <div class="sf-creation-layout">
    <div class="sf-thumbnails sf-stickers">
      <a href="#" class="sf-image sf-sticker" data-id="1">
        <img src="/images/sticker-1.png" />
      </a>
      <a href="#" class="sf-image sf-sticker" data-id="2">
        <img src="/images/sticker-2.png" />
      </a>
      <a href="#" class="sf-image sf-sticker" data-id="3">
        <img src="/images/sticker-3.png" />
      </a>
      <a href="#" class="sf-image sf-sticker" data-id="4">
        <img src="/images/sticker-4.png" />
      </a>
    </div>
    <div class="sf-create">
      <div class="camera-wrap">
        <div class="camera-holder">
          <video id="sf-video">Video stream not available.</video>
          <canvas class="hidden" id="sf-draw"></canvas>
          <div class="sf-actions">
            <button class="sf-action" id="sf-reset">Reset</button>
            <button class="sf-action" id="sf-confirm">Upload</button>
            <form class="sf-action active" id="sf-upload">
              <input type="file" id="sf-input-file">
            </form>
            <button class="sf-action" id="sf-capture"></button>
          </div>
          <img id="sf-picture">
        </div>
      </div>
    </div>
    <canvas class="hidden" id="sf-draw-intermediate"></canvas>
    <?=display_my_image_thumbnails()?>
  </div>
<?php

require_once __DIR__ . '/../components/footer.php';
