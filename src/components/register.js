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

export const initPageRegister = () => {
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
