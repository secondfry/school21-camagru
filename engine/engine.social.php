<?php

function like_toggle() {
  $data = getJSON();

  $imageID = $data->id ?? null;

  if (!$imageID) {
    ft_reset();
  }

  $stmt = DB::get()->prepare('SELECT * FROM `image_likes` WHERE `user_id` = ? AND `image_id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $_SESSION['user']['id'], PDO::PARAM_INT);
  $stmt->bindValue(2, $imageID, PDO::PARAM_INT);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    add_like($imageID);
  } else {
    remove_like($imageID);
  }
}

function add_like(string $imageID) {
  $stmt = DB::get()->prepare('INSERT INTO `image_likes` (user_id, image_id) VALUES (?, ?)');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $_SESSION['user']['id'], PDO::PARAM_INT);
  $stmt->bindValue(2, $imageID, PDO::PARAM_INT);
  $res = $stmt->execute();
}

function remove_like(string $imageID) {
  $stmt = DB::get()->prepare('DELETE FROM `image_likes` WHERE `user_id` = ? AND `image_id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $_SESSION['user']['id'], PDO::PARAM_INT);
  $stmt->bindValue(2, $imageID, PDO::PARAM_INT);
  $res = $stmt->execute();
}

function fetch_comments(string $id) {
  $stmt = DB::get()->prepare(
    '
SELECT `image_comments`.*,
       `u`.`username`
FROM `image_comments`
LEFT JOIN `users` `u` ON `image_comments`.`user_id` = `u`.`id`
WHERE `image_comments`.`image_id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $id, PDO::PARAM_INT);
  $res = $stmt->execute();
  return $stmt;
}

function perform_comment() {
  $comment = $_POST['comment'] ?? null;
  $imageID = $_POST['id'] ?? null;

  if (!$imageID || !$comment) {
    ft_reset_to('/?action=view&page=login');
  }

  $stmt = DB::get()->prepare('INSERT INTO `image_comments` (user_id, image_id, text) VALUES (?, ?, ?)');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $_SESSION['user']['id'], PDO::PARAM_INT);
  $stmt->bindValue(2, $imageID, PDO::PARAM_INT);
  $stmt->bindValue(3, $comment, PDO::PARAM_STR);
  $res = $stmt->execute();

  $stmt = DB::get()->prepare('
SELECT `email`, `notification`
FROM `users`
LEFT JOIN `images` `i` ON `users`.`id` = `i`.`user_id`
WHERE `i`.`id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $imageID, PDO::PARAM_INT);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $email_db = $row['email'] ?? null;
  $notification_db = $row['notification'] ?? '1';

  $stmt = DB::get()->prepare('SELECT `username` FROM `users` WHERE `id` = ?');
  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_STR);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  $username_db = $row['username'] ?? '';

  if ($email_db && $notification_db === '1') {
    $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/?action=view&page=image&id=' . $imageID;
    mail($email_db, '[oadhesiv\'s camagru] Новый комментарий', '
      К вашему шедевру [' . $actual_link . '] добавили комментарий:
      ' . $username_db . ': ' . $comment);
  }

  ft_reset_to('/?action=view&page=image&id=' . $imageID);
}

$GLOBALS['colors'] = [];

function get_color(string $userID) {
  if ($GLOBALS['colors'][$userID] ?? null) {
    return $GLOBALS['colors'][$userID];
  }

  $stmt = DB::get()->prepare('SELECT `flags` FROM `users` WHERE `id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $userID, PDO::PARAM_INT);
  $res = $stmt->execute();
  $row = $stmt->fetch();

  if (!$row) {
    return 'color-random';
  }

  switch ($row['flags']) {
    case 'admin':
      return 'color-admin';
    case 'wellknown':
      return 'color-wellknown';
  }

  return 'color-random';
}
