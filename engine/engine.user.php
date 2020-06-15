<?php

require_once __DIR__ . '/engine.user.name.php';
require_once __DIR__ . '/engine.user.email.php';
require_once __DIR__ . '/engine.user.password.php';

function user_register() {
  $name = $_POST['name'] ?? null;
  $email = $_POST['email'] ?? null;
  $pass = $_POST['passwd'] ?? null;

  $res = 0;
  if (!check_name($name)) {
    $res += 1;
  }
  if (!check_email($email)) {
    $res += 1;
  }
  if (!check_password($pass)) {
    $res += 1;
  }

  if ($res !== 0) {
    ft_reset_to('/?action=view&page=register');
  }

  $stmt = DB::get()->prepare('SELECT `id`, `username`, `email` FROM `users` WHERE `email` = ? OR `username` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $email, PDO::PARAM_STR);
  $stmt->bindValue(2, $name, PDO::PARAM_STR);
  $res = $stmt->execute();
  $errorFlag = false;
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $errorFlag = true;

    $name_db = $row['username'];
    $email_db = $row['email'];

    if ($name_db === $name) {
      $_SESSION['notification'][] = [
        'text' => 'Пользователь с таким именем уже зарегистрирован!',
        'type' => 'bad',
      ];
    }

    if ($email_db === $email) {
      $_SESSION['notification'][] = [
        'text' => 'Пользователь с таким адресом электронной почты уже зарегистрирован!',
        'type' => 'bad',
      ];
    }
  }

  if ($errorFlag) {
    $stmt->closeCursor();
    ft_reset();
  }
  $stmt->closeCursor();

  $stmt = DB::get()->prepare('INSERT INTO `users` (`username`, `email`, `password`) VALUES (?, ?, ?)');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $pass = hash('sha512', $pass);
  $stmt->bindValue(1, $name, PDO::PARAM_STR);
  $stmt->bindValue(2, $email, PDO::PARAM_STR);
  $stmt->bindValue(3, $pass, PDO::PARAM_STR);
  $res = $stmt->execute();
  if (!$res) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    $stmt->closeCursor();
    ft_reset();
  }
  $stmt->closeCursor();

  $id = DB::get()->lastInsertId();
  $uuid = UUID::v4();

  $stmt = DB::get()->prepare('INSERT INTO `confirmations` (`user_id`, `uuid`) VALUES (?, ?)');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $id, PDO::PARAM_INT);
  $stmt->bindValue(2, $uuid, PDO::PARAM_STR);
  $res = $stmt->execute();
  if (!$res) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    $stmt->closeCursor();
    ft_reset();
  }
  $stmt->closeCursor();

  $_SESSION['notification'][] = [
    'text' => 'Регистрация прошла успешно! Пожалуйста, подтвердите ваш email при помощи ссылки в письме.',
    'type' => 'good',
  ];

  $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/?action=confirm&uuid=' . $uuid;
  mail($email, 'Подтверждение регистрации на oadhesiv\'s camagru', 'Подтверджение – ' . $actual_link);

  ft_reset();
}

function user_login() {
  $name = $_POST['name'] ?? null;
  $pass = $_POST['passwd'] ?? null;

  if (!$name) {
    $_SESSION['notification'][] = [
      'text' => 'Вы не указали имя пользователя для входа!',
      'type' => 'bad',
    ];
  }

  if (!$pass) {
    $_SESSION['notification'][] = [
      'text' => 'Вы не указали пароль для входа!',
      'type' => 'bad',
    ];
  }

  if (!$name || !$pass) {
    ft_reset_to('/?action=view&page=login');
  }

  $stmt = DB::get()->prepare('SELECT `id`, `username`, `email`, `password`, `confirmed`, `notification` FROM `users` WHERE `username` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $name, PDO::PARAM_STR);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $id_db = $row['id'];
  $name_db = $row['username'];
  $email_db = $row['email'];
  $pass_db = $row['password'];
  $confirmed_db = $row['confirmed'];

  if (!$row) {
    $_SESSION['notification'][] = [
      'text' => 'Проверьте свой логин и пароль.',
      'type' => 'bad',
    ];
    $stmt->closeCursor();
    ft_reset_to('/?action=view&page=login');
  }
  $stmt->closeCursor();

  $pass = hash('sha512', $pass);
  if ($pass !== $pass_db) {
    $_SESSION['notification'][] = [
      'text' => 'Проверьте свой логин и пароль.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=login');
  }

  var_dump($row);

  if ($confirmed_db === "0") {
    $_SESSION['notification'][] = [
      'text' => 'Пожалуйста, подтвердите ваш email при помощи ссылки в письме.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=login');
  }

  $_SESSION['notification'][] = [
    'text' => 'Успешный вход ( ͡° ͜ʖ ͡°)',
    'type' => 'good',
  ];
  $_SESSION['user']['id'] = $id_db;
  $_SESSION['user']['name'] = $name_db;
  $_SESSION['user']['email'] = $email_db;
  ft_reset();
}

function user_logout() {
  session_destroy();
  session_start();
  $_SESSION['notification'][] = [
    'text' => 'Успешный выход ( ͡° ͜ʖ ͡°)',
    'type' => 'good',
  ];
  $_SESSION['user'] = [
    'id' => 0,
    'name' => '',
    'email' => '',
  ];
  ft_reset();
}

function user_confirm() {
  $uuid = $_GET['uuid'] ?? null;

  if (!$uuid) {
    ft_reset_to('/?action=view&page=login');
  }

  $stmt = DB::get()->prepare('SELECT `user_id` FROM `confirmations` WHERE `uuid` = ?');
  $stmt->bindValue(1, $uuid, PDO::PARAM_STR);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    ft_reset_to('/?action=view&page=login');
  }
  $stmt->closeCursor();

  $stmt = DB::get()->prepare('UPDATE `users` SET `confirmed` = 1 WHERE `id` = ?');
  $stmt->bindValue(1, $row['user_id'], PDO::PARAM_INT);
  $stmt->execute();

  $_SESSION['notification'][] = [
    'text' => 'Успешное подтверждение аккаунта. Теперь вы можете войти ( ͡° ͜ʖ ͡°)',
    'type' => 'good',
  ];
  ft_reset_to('/?action=view&page=login');
}
