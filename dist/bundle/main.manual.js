"use strict";

/**
 * capture.js
 */
var initPageCapture = function initPageCapture() {
  var width = 1280;
  var height = 720;
  var streaming = false;
  var allowed = false;
  var readyToUpload = false;
  var sfVideo = document.getElementById('sf-video');
  var sfPicture = document.getElementById('sf-picture');
  var sfDrawIntermediate = document.getElementById('sf-draw-intermediate');
  var sfDraw = document.getElementById('sf-draw');
  var sfCapture = document.getElementById('sf-capture');
  var sfStickers = document.querySelectorAll('.sf-sticker');
  var sfReset = document.getElementById('sf-reset');
  var sfConfirm = document.getElementById('sf-confirm');
  var sfInputFile = document.getElementById('sf-input-file');

  function afterPageLoad() {
    var _navigator;

    if (!((_navigator = navigator) === null || _navigator === void 0 ? void 0 : _navigator.mediaDevices)) {
      console.mlog('navigator.mediaDevices is undefined');
    } else {
      navigator.mediaDevices.getUserMedia({
        video: true,
        audio: false
      }).then(function (stream) {
        sfVideo.srcObject = stream;
        sfVideo.play();
      }).catch(console.merror.bind(console));
    }

    sfVideo.addEventListener('canplay', function (e) {
      if (streaming) {
        return;
      }

      height = sfVideo.videoHeight / (sfVideo.videoWidth / width);

      if (isNaN(height)) {
        height = width / (16 / 9);
      }

      sfVideo.setAttribute('width', width);
      sfVideo.setAttribute('height', height);
      sfDraw.setAttribute('width', width);
      sfDraw.setAttribute('height', height);
      streaming = true;
    }, false);
    sfCapture.addEventListener('click', function (e) {
      e.preventDefault();
      takePicture(sfVideo);
    }, false);
    sfStickers.forEach(function (elem) {
      elem.addEventListener('click', function (e) {
        e.preventDefault();
        elem.classList.toggle('selected');
        renderPicture();

        if (readyToUpload) {
          return;
        }

        var selected = document.querySelectorAll('.sf-sticker.selected');

        if (selected.length > 0) {
          allowed = true;
          sfCapture.classList.add('active');
        } else {
          allowed = false;
          sfCapture.classList.remove('active');
        }
      });
    });
    sfPicture.addEventListener('load', function () {
      return takePicture(sfPicture);
    });
    sfInputFile.addEventListener('change', function (e) {
      if (!sfInputFile.files || !sfInputFile.files[0]) {
        return;
      }

      sfPicture.src = URL.createObjectURL(sfInputFile.files[0]);
    });
    sfReset.addEventListener('click', function (e) {
      e.preventDefault();
      sfVideo.classList.remove('hidden');
      sfDraw.classList.add('hidden');
      sfCapture.classList.add('active');
      sfConfirm.classList.remove('active');
      sfReset.classList.remove('active');
      readyToUpload = false;
    });
    sfConfirm.addEventListener('click', function (e) {
      e.preventDefault();

      if (!readyToUpload) {
        return;
      }

      uploadPicture();
    });
  }

  function renderPicture() {
    var context = sfDraw.getContext('2d');
    sfDraw.width = width;
    sfDraw.height = height;
    context.drawImage(sfDrawIntermediate, 0, 0, width, height);
    var orig_ratio = width / height;
    var final_w;
    var final_h;
    var offset_x;
    var offset_y;

    if (orig_ratio > 16 / 9) {
      final_w = 16 * height / 9;
      final_h = height;
      offset_x = (width - final_w) / 2;
      offset_y = 0;
    } else {
      final_w = width;
      final_h = 9 * width / 16;
      offset_x = 0;
      offset_y = (height - final_h) / 2;
    }

    document.querySelectorAll('.sf-sticker.selected img').forEach(function (e) {
      return context.drawImage(e, offset_x, offset_y, final_w, final_h);
    });
  }

  function takePicture(parentElem) {
    if (!width || !height) {
      return;
    }

    if (parentElem === sfVideo && !sfCapture.classList.contains('active')) {
      return;
    }

    var contextIntermediate = sfDrawIntermediate.getContext('2d');
    sfDrawIntermediate.width = width;
    sfDrawIntermediate.height = height;
    contextIntermediate.drawImage(parentElem, 0, 0, width, height);
    renderPicture();
    sfVideo.classList.add('hidden');
    sfDraw.classList.remove('hidden');
    sfCapture.classList.remove('active');
    sfConfirm.classList.add('active');
    sfReset.classList.add('active');
    readyToUpload = true;
  }

  function uploadPicture() {
    var data = sfDraw.toDataURL('image/jpeg', 0.8);
    var selectedIDs = [].map.call(document.querySelectorAll('.sf-sticker.selected'), function (e) {
      return e.dataset['id'];
    });
    fetch('/?action=upload', {
      method: 'POST',
      body: JSON.stringify({
        data,
        stickers: selectedIDs
      })
    }).then(function (res) {
      if (res.status === 413) {
        alert('Selected image size is too big!');
      }

      res.text().then(console.mlog.bind(console)).catch(console.merror.bind(console));
      document.location.href = document.location.href;
    }).catch(console.merror.bind(console));
  }

  window.addEventListener('load', afterPageLoad, false);
};
/**
 * common.js
 */


var initLikes = function initLikes() {
  var likeButtons = document.querySelectorAll('.sf-action-like');
  likeButtons.forEach(function (lb) {
    return lb.addEventListener('click', function (e) {
      e.preventDefault();

      if (!userLogon) {
        document.location.href = '/?action=view&page=login';
      }

      var id = lb.dataset['id'];
      fetch('/?action=like_toggle', {
        method: 'POST',
        body: JSON.stringify({
          id
        })
      }).then(function (res) {
        res.text().then(console.mlog.bind(console)).catch(console.merror.bind(console));
        document.location.href = document.location.href;
      }).catch(console.merror.bind(console));
    });
  });
  var removeTexts = document.querySelectorAll('.sf-remove');
  removeTexts.forEach(function (rt) {
    return rt.addEventListener('click', function (e) {
      e.preventDefault();
      var id = rt.dataset['id'];
      fetch('/?action=remove', {
        method: 'POST',
        body: JSON.stringify({
          id
        })
      }).then(function (res) {
        res.text().then(console.mlog.bind(console)).catch(console.merror.bind(console));
        document.location.href = document.location.href;
      }).catch(console.merror.bind(console));
    });
  });
};

var initCommon = function initCommon() {
  initLikes();
};
/**
 * register.js
 */


var checkLength = function checkLength(str, size, message) {
  if (str.length >= size) {
    return false;
  }

  console.merror(message);
  alert(message);
  return true;
};

var getTrimmedValue = function getTrimmedValue(query) {
  var elem = document.querySelector(query);
  var value = elem.value.trim();
  elem.value = value;
  return value;
};

var checkEmail = function checkEmail() {
  var value = getTrimmedValue('#email');
  var errorFlag = false;
  errorFlag = checkLength(value, 3, 'Your email length must be at least 3 symbols.') || errorFlag;

  if (!errorFlag) {
    console.mlog(`Email [${value}] is OK.`);
  }

  return errorFlag;
};

var checkPassword = function checkPassword() {
  var value = getTrimmedValue('#password');
  var errorFlag = false;
  errorFlag = checkLength(value, 8, 'Your password length must be at least 8 symbols.') || errorFlag;
  var match = value.match(/[A-Z]/);

  if (!match) {
    var message = 'Your password must contain uppercase letter.';
    console.merror(message);
    alert(message);
    errorFlag = true;
  }

  if (!errorFlag) {
    console.mlog(`Password [${value}] is OK.`);
  }

  return errorFlag;
};

var checkUsername = function checkUsername() {
  var value = getTrimmedValue('#username');
  var errorFlag = false;
  errorFlag = checkLength(value, 3, 'Your username length must be at least 1 symbol.') || errorFlag;

  if (!errorFlag) {
    console.mlog(`Username [${value}] is OK.`);
  }

  return errorFlag;
};

var initPageRegister = function initPageRegister() {
  var elemForm = document.querySelector('#register-form');
  elemForm.addEventListener('submit', function (e) {
    var errorFlag = false;
    errorFlag = checkEmail() || errorFlag;
    errorFlag = checkPassword() || errorFlag;
    errorFlag = checkUsername() || errorFlag;

    if (errorFlag) {
      e.preventDefault();
    }
  });
};
/**
 * console.js
 */


var initConsole = function initConsole() {
  console.mlog = function (msg) {
    if (console.isDevelopmentBuild) {
      return console.log(msg);
    }
  };

  console.merror = function (msg) {
    if (console.isDevelopmentBuild) {
      return console.error(msg);
    }
  };
};
/**
 * app.js
 */

/**
 * In production should be set to false.
 * This will suppress console output (this is sadly enforced by task subject).
 */


console.isDevelopmentBuild = false;
initConsole();
var PAGE_CAPTURE = '?action=view&page=create';
var PAGE_REGISTER = '?action=view&page=register';

switch (window.location.search) {
  case PAGE_CAPTURE:
    initPageCapture();
    break;

  case PAGE_REGISTER:
    initPageRegister();
    break;
}

initCommon();
