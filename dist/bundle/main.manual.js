/**
 * capture.js
 */
const initPageCapture = () => {
  const width = 1280;
  let height = 720;
  let streaming = false;
  let allowed = false;
  let readyToUpload = false;

  const sfVideo = document.getElementById('sf-video');
  const sfPicture = document.getElementById('sf-picture');
  const sfDrawIntermediate = document.getElementById('sf-draw-intermediate');
  const sfDraw = document.getElementById('sf-draw');
  const sfCapture = document.getElementById('sf-capture');
  const sfStickers = document.querySelectorAll('.sf-sticker');
  const sfReset = document.getElementById('sf-reset');
  const sfConfirm = document.getElementById('sf-confirm');
  const sfInputFile = document.getElementById('sf-input-file');

  function afterPageLoad() {
    if (!navigator?.mediaDevices) {
      console.mlog('navigator.mediaDevices is undefined');
      return;
    }

    navigator.mediaDevices
      .getUserMedia({ video: true, audio: false })
      .then(function (stream) {
        sfVideo.srcObject = stream;
        sfVideo.play();
      })
      .catch(console.merror.bind(console));

    sfVideo.addEventListener(
      'canplay',
      function (e) {
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
      },
      false
    );

    sfCapture.addEventListener(
      'click',
      (e) => {
        e.preventDefault();

        takePicture(sfVideo);
      },
      false
    );

    sfStickers.forEach((elem) => {
      elem.addEventListener('click', (e) => {
        e.preventDefault();

        elem.classList.toggle('selected');

        renderPicture();

        if (readyToUpload) {
          return;
        }

        const selected = document.querySelectorAll('.sf-sticker.selected');
        if (selected.length > 0) {
          allowed = true;
          sfCapture.classList.add('active');
        } else {
          allowed = false;
          sfCapture.classList.remove('active');
        }
      });
    });

    sfPicture.addEventListener('load', () => takePicture(sfPicture));

    sfInputFile.addEventListener('change', e => {
      if (!sfInputFile.files || !sfInputFile.files[0]) {
        return;
      }

      sfPicture.src = URL.createObjectURL(sfInputFile.files[0]);
    });

    sfReset.addEventListener('click', (e) => {
      e.preventDefault();

      sfVideo.classList.remove('hidden');
      sfDraw.classList.add('hidden');

      sfCapture.classList.add('active');
      sfConfirm.classList.remove('active');
      sfReset.classList.remove('active');

      readyToUpload = false;
    });

    sfConfirm.addEventListener('click', (e) => {
      e.preventDefault();

      if (!readyToUpload) {
        return;
      }

      uploadPicture();
    });
  }

  function renderPicture() {
    const context = sfDraw.getContext('2d');
    sfDraw.width = width;
    sfDraw.height = height;
    context.drawImage(sfDrawIntermediate, 0, 0, width, height);

    const orig_ratio = width / height;
    let final_w;
    let final_h;
    let offset_x;
    let offset_y;
    if (orig_ratio > 16 / 9) {
      final_w = (16 * height) / 9;
      final_h = height;
      offset_x = (width - final_w) / 2;
      offset_y = 0;
    } else {
      final_w = width;
      final_h = (9 * width) / 16;
      offset_x = 0;
      offset_y = (height - final_h) / 2;
    }
    document
      .querySelectorAll('.sf-sticker.selected img')
      .forEach((e) =>
        context.drawImage(e, offset_x, offset_y, final_w, final_h)
      );
  }

  function takePicture(parentElem) {
    if (!width || !height) {
      return;
    }

    if (parentElem === sfVideo && !sfCapture.classList.contains('active')) {
      return;
    }

    const contextIntermediate = sfDrawIntermediate.getContext('2d');
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
    const data = sfDraw.toDataURL('image/jpeg', 0.8);
    const selectedIDs = [].map.call(
      document.querySelectorAll('.sf-sticker.selected'),
      (e) => e.dataset['id']
    );

    fetch('/?action=upload', {
      method: 'POST',
      body: JSON.stringify({
        data,
        stickers: selectedIDs,
      }),
    })
      .then((res) => {
        if (res.status === 413) {
          alert('Selected image size is too big!');
        }
        res
          .text()
          .then(console.mlog.bind(console))
          .catch(console.merror.bind(console));
        document.location.href = document.location.href;
      })
      .catch(console.merror.bind(console));
  }

  window.addEventListener('load', afterPageLoad, false);
};

/**
 * common.js
 */
const initLikes = () => {
  const likeButtons = document.querySelectorAll('.sf-action-like');

  likeButtons.forEach((lb) =>
    lb.addEventListener('click', (e) => {
      e.preventDefault();

      if (!userLogon) {
        document.location.href = '/?action=view&page=login';
      }

      const id = lb.dataset['id'];
      fetch('/?action=like_toggle', {
        method: 'POST',
        body: JSON.stringify({
          id,
        }),
      })
        .then((res) => {
          res
            .text()
            .then(console.mlog.bind(console))
            .catch(console.merror.bind(console));
          document.location.href = document.location.href;
        })
        .catch(console.merror.bind(console));
    })
  );

  const removeTexts = document.querySelectorAll('.sf-remove');

  removeTexts.forEach((rt) =>
    rt.addEventListener('click', (e) => {
      e.preventDefault();

      const id = rt.dataset['id'];
      fetch('/?action=remove', {
        method: 'POST',
        body: JSON.stringify({
          id,
        }),
      })
        .then((res) => {
          res
            .text()
            .then(console.mlog.bind(console))
            .catch(console.merror.bind(console));
          document.location.href = document.location.href;
        })
        .catch(console.merror.bind(console));
    })
  );
};

const initCommon = () => {
  initLikes();
};

/**
 * register.js
 */

const checkLength = (str, size, message) => {
  if (str.length >= size) {
    return false;
  }

  console.merror(message);
  alert(message);
  return true;
};

const getTrimmedValue = (query) => {
  const elem = document.querySelector(query);
  const value = elem.value.trim();
  elem.value = value;

  return value;
};

const checkEmail = () => {
  const value = getTrimmedValue('#email');
  let errorFlag = false;

  errorFlag = checkLength(value, 3, 'Your email length must be at least 3 symbols.') || errorFlag;

  if (!errorFlag) {
    console.mlog(`Email [${value}] is OK.`);
  }

  return errorFlag;
};

const checkPassword = () => {
  const value = getTrimmedValue('#password');
  let errorFlag = false;

  errorFlag = checkLength(value, 8, 'Your password length must be at least 8 symbols.') || errorFlag;

  const match = value.match(/[A-Z]/);
  if (!match) {
    const message = 'Your password must contain uppercase letter.';
    console.merror(message);
    alert(message);
    errorFlag = true;
  }

  if (!errorFlag) {
    console.mlog(`Password [${value}] is OK.`);
  }

  return errorFlag;
};

const checkUsername = () => {
  const value = getTrimmedValue('#username');
  let errorFlag = false;

  errorFlag = checkLength(value, 3, 'Your username length must be at least 1 symbol.') || errorFlag;

  if (!errorFlag) {
    console.mlog(`Username [${value}] is OK.`);
  }

  return errorFlag;
};

const initPageRegister = () => {
  const elemForm = document.querySelector('#register-form');

  elemForm.addEventListener('submit', (e) => {
    let errorFlag = false;

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
const initConsole = () => {
  console.mlog = (msg) => {
    if (console.isDevelopmentBuild) {
      return console.log(msg);
    }
  };

  console.merror = (msg) => {
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

const PAGE_CAPTURE = '?action=view&page=create';
const PAGE_REGISTER = '?action=view&page=register';

switch (window.location.search) {
  case PAGE_CAPTURE:
    initPageCapture();
    break;
  case PAGE_REGISTER:
    initPageRegister();
    break;
}

initCommon();
