<?php

function check_name_existence(?string $name): bool {
  if ($name) {
    return true;
  }

  $_SESSION['notification'][] = [
    'text' => 'Вы не указали имя пользователя!',
    'type' => 'bad',
  ];

  return false;
}

function check_name(?string &$name): bool {
  if (!check_name_existence($name)) {
    return false;
  }

  $name = trim($name);

  $res = 0;

  if ($res !== 0) {
    return false;
  }

  return true;
}
