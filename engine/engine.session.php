<?php

session_start();

if (empty($_SESSION['notification'])) {
  $_SESSION['notification'] = [];
}

if (empty($_SESSION['user'])) {
  $_SESSION['user'] = [
    'id' => 0,
    'username' => '',
    'email' => '',
  ];
}

if (empty($_SESSION['page'])) {
  $_SESSION['page'] = '/';
}
