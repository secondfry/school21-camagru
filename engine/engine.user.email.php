<?php

function check_email_existence(?string $email): bool {
  if ($email) {
    return true;
  }

  $_SESSION['notification'][] = [
    'text' => 'Вы не указали электронную почту при регистрации!',
    'type' => 'bad',
  ];

  return false;
}

function check_email(?string &$email): bool {
  if (!check_email_existence($email)) {
    return false;
  }

  $email = trim($email);

  $res = 0;

  if ($res !== 0) {
    return false;
  }

  return true;
}
