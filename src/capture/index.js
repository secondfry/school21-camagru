(() => {
  const width = 1280;
  let height = 0;
  let streaming = false;
  const video = document.getElementById('sf-video');
  const canvas = document.getElementById('sf-draw');
  const photo = document.getElementById('photo');
  const startbutton = document.getElementById('sf-capture');
  let error = null;

  function afterPageLoad() {
    if (!navigator?.mediaDevices) {
      if (window.isDevelopmentBuild) {
        console.log('navigator.mediaDevices is undefined');
      }

      clearPhoto();
      return;
    }

    navigator.mediaDevices.getUserMedia({video: true, audio: false})
             .then(function(stream) {
               video.srcObject = stream;
               video.play();
             })
             .catch(function(err) {
               error = err;
             });

    video.addEventListener('canplay', function(ev){
      if (streaming) {
        return;
      }

      height = video.videoHeight / (video.videoWidth/width);

      // Firefox currently has a bug where the height can't be read from
      // the video, so we will make assumptions if this happens.

      if (isNaN(height)) {
        height = width / (4/3);
      }

      video.setAttribute('width', width);
      video.setAttribute('height', height);
      canvas.setAttribute('width', width);
      canvas.setAttribute('height', height);
      streaming = true;
    }, false);

    startbutton.addEventListener('click', function(ev){
      takepicture();
      ev.preventDefault();
    }, false);

    clearPhoto();
  }

  function clearPhoto() {
    const context = canvas.getContext('2d');
    context.fillStyle = "#AAA";
    context.fillRect(0, 0, canvas.width, canvas.height);

    const data = canvas.toDataURL('image/png');
    photo.setAttribute('src', data);
  }

  // Capture a photo by fetching the current contents of the video
  // and drawing it into a canvas, then converting that to a PNG
  // format data URL. By drawing it on an offscreen canvas and then
  // drawing that to the screen, we can change its size and/or apply
  // other changes before drawing it.

  function takepicture() {
    if (!width || !height) {
      clearPhoto();
      return;
    }

    const context = canvas.getContext('2d');
    canvas.width = width;
    canvas.height = height;
    context.drawImage(video, 0, 0, width, height);

    const data = canvas.toDataURL('image/png');
    photo.setAttribute('src', data);
  }

  window.addEventListener('load', afterPageLoad, false);
})();
