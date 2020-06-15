<?php

function check_password_existence(?string $pass): bool {
  if ($pass) {
    return true;
  }

  $_SESSION['notification'][] = [
    'text' => 'Вы не указали пароль!',
    'type' => 'bad',
  ];

  return false;
}

function check_password_regexp(string $pass): bool {
  $res = preg_match('/[A-Z]/', $pass);
  if ($res) {
    return true;
  }

  $_SESSION['notification'][] = [
    'text' => 'В пароле должна быть хотя бы одна заглавная буква!',
    'type' => 'bad',
  ];

  return false;
}

function check_password_length(string $pass): bool {
  if (strlen($pass) >= 8) {
    return true;
  }

  $_SESSION['notification'][] = [
    'text' => 'Длина пароля должен быть как минимум 8 символов!',
    'type' => 'bad',
  ];

  return false;
}

function check_password(?string &$pass): bool {
  if (!check_password_existence($pass)) {
    return false;
  }

  $pass = trim($pass);

  $res = 0;
  if (!check_password_regexp($pass)) {
    $res += 1;
  }
  if (!check_password_length($pass)) {
    $res += 1;
  }

  if ($res !== 0) {
    return false;
  }

  return true;
}
