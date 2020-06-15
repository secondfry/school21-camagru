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
