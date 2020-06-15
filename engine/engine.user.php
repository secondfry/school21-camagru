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

    $name_db = $row['username'] ?? null;
    $email_db = $row['email'] ?? null;

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
  mail($email, '[oadhesiv\'s camagru] Подтверждение регистрации', 'Подтверджение – ' . $actual_link);

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
  $name_db = $row['username'] ?? null;
  $email_db = $row['email'] ?? null;
  $pass_db = $row['password'] ?? null;
  $confirmed_db = $row['confirmed'] ?? null;

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

  if (!$uuid || !UUID::is_valid($uuid)) {
    ft_reset_to('/?action=view&page=login');
  }

  $stmt = DB::get()->prepare('SELECT `user_id` FROM `confirmations` WHERE `used` = 0 AND `uuid` = ?');
  $stmt->bindValue(1, $uuid, PDO::PARAM_STR);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    ft_reset_to('/?action=view&page=login');
  }
  $stmt->closeCursor();

  $id = $row['user_id'] ?? null;
  if (!$id) {
    ft_reset_to('/?action=view&page=login');
  }

  $stmt = DB::get()->prepare('UPDATE `users` SET `confirmed` = 1 WHERE `id` = ?');
  $stmt->bindValue(1, $row['user_id'], PDO::PARAM_INT);
  $stmt->execute();
  $stmt = DB::get()->prepare('UPDATE `confirmations` SET `used` = 1 WHERE `uuid` = ?');
  $stmt->bindValue(1, $uuid, PDO::PARAM_STR);
  $stmt->execute();

  $_SESSION['notification'][] = [
    'text' => 'Успешное подтверждение аккаунта. Теперь вы можете войти ( ͡° ͜ʖ ͡°)',
    'type' => 'good',
  ];
  ft_reset_to('/?action=view&page=login');
}

function user_recover_initiate() {
  $email = $_POST['email'] ?? null;

  if (!$email) {
    ft_reset_to('/?action=view&page=recover');
  }

  $stmt = DB::get()->prepare('SELECT `id` FROM `users` WHERE `email` = ?');
  $stmt->bindValue(1, $email, PDO::PARAM_STR);
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    $_SESSION['notification'][] = [
      'text' => 'Вам следует вначале зарегистроваться.',
    ];
    ft_reset();
  }
  $id = $row['id'] ?? null;

  if (!$id) {
    ft_reset_to('/?action=view&page=recover_step_1');
  }

  $uuid = UUID::v4();
  $stmt = DB::get()->prepare('INSERT INTO `recovers` (`user_id`, `uuid`) VALUES (?, ?)');
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
    'text' => 'Ссылка для сброса пароля отправлена вам на почту.',
    'type' => 'good',
  ];

  $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/?action=view&page=recover_step_2&uuid=' . $uuid;
  mail($email, '[oadhesiv\'s camagru] Восстановление пароля', 'Восстановление пароля – ' . $actual_link);

  ft_reset();
}

function user_recover_perform() {
  $uuid = $_POST['uuid'] ?? null;
  $pass = $_POST['passwd'] ?? null;

  if (!$uuid || !UUID::is_valid($uuid)) {
    ft_reset_to('/?action=view&page=recover_step_1');
  }

  $res = 0;
  if (!check_password($pass)) {
    $res += 1;
  }

  if ($res !== 0) {
    ft_reset_to('/?action=view&page=recover_step_2&uuid=' . $uuid);
  }

  $stmt = DB::get()->prepare('SELECT `user_id` FROM `recovers` WHERE `used` = 0 AND `uuid` = ?');
  $stmt->bindValue(1, $uuid, PDO::PARAM_STR);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    ft_reset_to('/?action=view&page=recover_step_1');
  }
  $stmt->closeCursor();

  $id = $row['user_id'] ?? null;
  if (!$id) {
    ft_reset_to('/?action=view&page=recover_step_1');
  }

  $stmt = DB::get()->prepare('UPDATE `users` SET `password` = ? WHERE `id` = ?');
  $pass = hash('sha512', $pass);
  $stmt->bindValue(2, $row['user_id'], PDO::PARAM_INT);
  $stmt->bindValue(1, $pass, PDO::PARAM_STR);
  $stmt->execute();
  $stmt = DB::get()->prepare('UPDATE `recovers` SET `used` = 1 WHERE `uuid` = ?');
  $stmt->bindValue(1, $uuid, PDO::PARAM_STR);
  $stmt->execute();

  $_SESSION['notification'][] = [
    'text' => 'Успешное восстановление аккаунта. Теперь вы можете войти ( ͡° ͜ʖ ͡°)',
    'type' => 'good',
  ];
  ft_reset_to('/?action=view&page=login');
}

function check_uuid(string $table) {
  $uuid = $_GET['uuid'] ?? null;

  if (!$uuid || !UUID::is_valid($uuid)) {
    ft_reset_to('/');
  }

  switch ($table) {
    case 'recovers':
      $stmt = DB::get()->prepare('SELECT `used` FROM `recovers` WHERE `uuid` = ?');
      break;
    case 'confirmations':
      $stmt = DB::get()->prepare('SELECT `used` FROM `confirmations` WHERE `uuid` = ?');
      break;
  }

  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $uuid, PDO::PARAM_STR);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row || $row['used']) {
    $_SESSION['notification'][] = [
      'text' => 'Вы не можете использовать одну и ту же ссылку дважды.',
      'type' => 'bad',
    ];
    ft_reset();
  }
}

function change_username() {
  $name = $_POST['name'] ?? null;
  $old_passwd = $_POST['passwd'] ?? null;

  $res = 0;
  if (!check_name($name)) {
    $res += 1;
  }

  if ($res !== 0) {
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->query('SELECT `password` FROM `users` WHERE `id` = ' . $_SESSION['user']['id']);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $passwd_db = $row['password'] ?? null;

  if ($passwd_db !== hash('sha512', $old_passwd)) {
    $_SESSION['notification'][] = [
      'text' => 'Вы указываете неверный пароль.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->prepare('SELECT 1 FROM `users` WHERE `username` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $name, PDO::PARAM_STR);
  $res = $stmt->execute();
  $errorFlag = false;
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $errorFlag = true;

    $_SESSION['notification'][] = [
      'text' => 'Пользователь с таким именем уже зарегистрирован!',
      'type' => 'bad',
    ];
  }

  if ($errorFlag) {
    $stmt->closeCursor();
    ft_reset_to('/?action=view&page=user');
  }
  $stmt->closeCursor();

  $stmt = DB::get()->prepare('UPDATE `users` SET `username` = ? WHERE `id` = ' . $_SESSION['user']['id']);
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $stmt->bindValue(1, $name, PDO::PARAM_STR);
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
    'text' => 'Имя пользователя успешно изменено!',
    'type' => 'good',
  ];
  $_SESSION['user']['username'] = $name;

  ft_reset_to('/?action=view&page=user');
}

function change_email() {
  $email = $_POST['email'] ?? null;
  $old_passwd = $_POST['passwd'] ?? null;

  $res = 0;
  if (!check_email($email)) {
    $res += 1;
  }

  if ($res !== 0) {
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->query('SELECT `password` FROM `users` WHERE `id` = ' . $_SESSION['user']['id']);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $pass_wd = $row['password'] ?? null;

  if ($pass_wd !== hash('sha512', $old_passwd)) {
    $_SESSION['notification'][] = [
      'text' => 'Вы указываете неверный пароль.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->prepare('SELECT 1 FROM `users` WHERE `email` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $email, PDO::PARAM_STR);
  $res = $stmt->execute();
  $errorFlag = false;
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $errorFlag = true;

    $_SESSION['notification'][] = [
      'text' => 'Пользователь с таким адресом электронной почты уже зарегистрирован!',
      'type' => 'bad',
    ];
  }

  if ($errorFlag) {
    $stmt->closeCursor();
    ft_reset_to('/?action=view&page=user');
  }
  $stmt->closeCursor();

  $stmt = DB::get()->prepare('UPDATE `users` SET `email` = ?, `confirmed` = 0 WHERE `id` = ' . $_SESSION['user']['id']);
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $stmt->bindValue(1, $email, PDO::PARAM_STR);
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
    'text' => 'Адрес электронной почты успешно изменен! Теперь вам нужно его подтвердить.',
    'type' => 'good',
  ];
  $_SESSION['user']['email'] = $email;

  $uuid = UUID::v4();

  $stmt = DB::get()->prepare('INSERT INTO `confirmations` (`user_id`, `uuid`) VALUES (?, ?)');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
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

  $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/?action=confirm&uuid=' . $uuid;
  mail($email, '[oadhesiv\'s camagru] Подтверждение смены электронной почты', 'Подтверджение – ' . $actual_link);

  ft_reset_to('/?action=view&page=user');
}

function change_password() {
  $new_passwd = $_POST['new_passwd'] ?? null;
  $old_passwd = $_POST['old_passwd'] ?? null;

  $res = 0;
  if (!check_password($new_passwd)) {
    $res += 1;
  }

  if ($res !== 0) {
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->query('SELECT `password` FROM `users` WHERE `id` = ' . $_SESSION['user']['id']);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $pass_wd = $row['password'] ?? null;

  if ($pass_wd !== hash('sha512', $old_passwd)) {
    $_SESSION['notification'][] = [
      'text' => 'Вы указываете неверный пароль.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->prepare('UPDATE `users` SET `password` = ? WHERE `id` = ' . $_SESSION['user']['id']);
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $stmt->bindValue(1, hash('sha512', $new_passwd), PDO::PARAM_STR);
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
    'text' => 'Ваш пароль успешно изменен!',
    'type' => 'good',
  ];

  ft_reset_to('/?action=view&page=user');
}

function change_notification() {
  $notification = $_POST['notification'] ?? null;

  if ($notification === null) {
    $notification_db = 0;
  } elseif ($notification === 'on') {
    $notification_db = 1;
  } else {
    ft_reset_to('/?action=view&page=user');
  }

  $stmt = DB::get()->query('UPDATE `users` SET `notification` = ' . $notification_db . ' WHERE `id` = ' . $_SESSION['user']['id']);
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset_to('/?action=view&page=user');
  }

  $_SESSION['notification'][] = [
    'text' => 'Ваши настройки уведомлений успешно изменены!',
    'type' => 'good',
  ];

  ft_reset_to('/?action=view&page=user');
}
