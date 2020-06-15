<?php

function image_upload() {
  $data = getJSON();

  $image    = $data->data ?? null;
  $stickers = $data->stickers ?? null;

  if (!$image || !is_array($stickers)) {
    exit;
  }

  $image               = str_replace('data:image/jpeg;base64,', '', $image);
  $image               = str_replace(' ', '+', $image);
  $raw_data            = base64_decode($image);
  $filename            = UUID::v4() . '.jpg';
  $pathServerDirectory = __DIR__ . '/../dist/static/';
  $pathServer          = $pathServerDirectory . $filename;
  $pathClient          = '/static/' . $filename;

  if (!file_exists($pathServerDirectory)) {
    mkdir($pathServerDirectory, 0775, true);
  }

  file_put_contents($pathServer, $raw_data);
  $check = getimagesize($pathServer);
  if ($check === false) {
    $_SESSION['notification'][] = [
      'text' => 'owo',
      'type' => 'bad',
    ];
    unlink($pathServer);
    exit;
  }

  $orig       = imagecreatefromjpeg($pathServer);
  $orig_w     = imagesx($orig);
  $orig_h     = imagesy($orig);
  $orig_ratio = $orig_w / $orig_h;
  if ($orig_ratio > 16 / 9) {
    $final_w  = 16 * $orig_h / 9;
    $final_h  = $orig_h;
    $offset_x = ($orig_w - $final_w) / 2;
    $offset_y = 0;
  } else {
    $final_w  = $orig_w;
    $final_h  = 9 * $orig_w / 16;
    $offset_x = 0;
    $offset_y = ($orig_h - $final_h) / 2;
  }
  $final = imagecrop($orig, ['x' => $offset_x, 'y' => $offset_y, 'width' => $final_w, 'height' => $final_h]);
  foreach ($stickers as $v) {
    $status = preg_match("/[0-9]+/", $v, $res);
    if (!$status) {
      continue;
    }

    $stickerPath = __DIR__ . '/../dist/images/sticker-' . $res[0] . '.png';
    $sticker     = imagecreatefrompng($stickerPath);
    imagecopyresized($final, $sticker, 0, 0, 0, 0, $final_w, $final_h, 1280, 720);
    imagedestroy($sticker);
  }
  if ($final !== false) {
    unlink($pathServer);
    imagepng($final, $pathServer);
    imagedestroy($final);
  }
  imagedestroy($orig);

  $stmt = DB::get()->prepare('INSERT INTO `images` (`user_id`, `path`) VALUES (?, ?)');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $_SESSION['user']['id'], PDO::PARAM_INT);
  $stmt->bindValue(2, $pathClient, PDO::PARAM_STR);
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

  chmod($pathServer, 0664);
}

function get_all_images(int $page) {
  $stmt = DB::get()->prepare(
    '
SELECT `images`.*,
       COUNT(DISTINCT `il`.`id`) AS `likes`,
       COUNT(DISTINCT `ic`.`id`) AS `comments`,
       `u`.`username`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_likes` `ilm` ON `images`.`id` = `ilm`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
LEFT JOIN `users` `u` ON `images`.`user_id` = `u`.`id`
GROUP BY `images`.`id`
ORDER BY `created` DESC
LIMIT ?,16'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $page * 16, PDO::PARAM_INT);
  $res = $stmt->execute();
  return $stmt;
}

function get_most_liked() {
  $stmt = DB::get()->query(
    '
SELECT `images`.*,
       COUNT(DISTINCT `il`.`id`) AS `likes`,
       COUNT(DISTINCT `ic`.`id`) AS `comments`,
       `u`.`username`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
LEFT JOIN `users` `u` ON `images`.`user_id` = `u`.`id`
GROUP BY `images`.`id`
HAVING COUNT(`il`.`id`) > 0
ORDER BY `likes` DESC, `created` DESC
LIMIT 8'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  return $stmt;
}

function get_most_commented() {
  $stmt = DB::get()->query(
    '
SELECT `images`.*,
       COUNT(DISTINCT `il`.`id`) AS `likes`,
       COUNT(DISTINCT `ic`.`id`) AS `comments`,
       `u`.`username`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
LEFT JOIN `users` `u` ON `images`.`user_id` = `u`.`id`
GROUP BY `images`.`id`
HAVING `comments` > 0
ORDER BY `comments` DESC, `created` DESC
LIMIT 8'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  return $stmt;
}

function get_user_images(int $id) {
  $stmt = DB::get()->prepare(
    '
SELECT `images`.*,
       COUNT(DISTINCT `il`.`id`) AS `likes`,
       COUNT(DISTINCT `ic`.`id`) AS `comments`,
       `u`.`username`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
LEFT JOIN `users` `u` ON `images`.`user_id` = `u`.`id`
WHERE `images`.`user_id` = ?
GROUP BY `images`.`id`
ORDER BY `created` DESC
LIMIT 16'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $id, PDO::PARAM_INT);
  $stmt->execute();
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  return $stmt;
}

function display_query_thumbnails(PDOStatement $stmt) {
  $mylikes = all_my_image_likes();
  $i = 0;
  ?>
  <div class="sf-thumbnails">
    <?php
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $liked = $mylikes[$row['id']] ?? false;
      ?>
      <div class="sf-image">
        <a href="/?action=view&page=image&id=<?= $row['id'] ?>">
          <img src="<?= $row['path'] ?>"/>
        </a>
        <div class="sf-image-info">
          <a class="sf-image-icon <?php if ($liked !== false) echo 'sf-image-likes-solid-icon'; else echo 'sf-image-likes-icon'; ?> sf-action-like" href="#" data-id="<?= $row['id'] ?>"></a>
          <span class="sf-counter"><?= $row['likes'] ?></span>
          <span class="sf-image-icon sf-image-comments-icon"></span>
          <span class="sf-counter"><?= $row['comments'] ?></span>
          <span class="sf-username <?=get_color($row['user_id'])?>"><?= htmlentities($row['username']) ?></span>
        </div>
      </div>
      <?php
      $i += 1;
    }
    ?>
  </div>
  <?php
  return $i;
}

function display_user_image_thumbnails(int $id) {
  $stmt = get_user_images($id);
  display_query_thumbnails($stmt);
}

function display_my_image_thumbnails() {
  display_user_image_thumbnails($_SESSION['user']['id']);
}

function fetch_image(string $id) {
  $stmt = DB::get()->prepare(
    '
SELECT `images`.*,
       COUNT(DISTINCT `il`.`id`) AS `likes`,
       COUNT(DISTINCT `ic`.`id`) AS `comments`,
       `u`.`username`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
LEFT JOIN `users` `u` ON `images`.`user_id` = `u`.`id`
WHERE `images`.`id` = ?
GROUP BY `images`.`id`
LIMIT 1'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $id, PDO::PARAM_INT);
  $res = $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) {
    ft_reset();
  }

  $stmt = DB::get()->prepare(
    '
SELECT `images`.`id`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
WHERE `il`.`user_id` = ? AND `images`.`id` = ?
LIMIT 1'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
  $stmt->bindValue(2, $id, PDO::PARAM_INT);
  $res = $stmt->execute();
  $row_likes = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row_likes) {
    $row['liked'] = 0;
  } else {
    $row['liked'] = 1;
  }

  return $row;
}

function image_remove() {
  $data = getJSON();

  $imageID = $data->id ?? null;
  if (!$imageID) {
    ft_reset();
  }

  $stmt = DB::get()->prepare('DELETE FROM `images` WHERE `user_id` = ? AND `id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
  $stmt->bindValue(2, $imageID, PDO::PARAM_INT);
  $res = $stmt->execute();
}

$GLOBALS['my_likes'] = null;

function all_my_image_likes() {
  if ($GLOBALS['my_likes']) {
    return $GLOBALS['my_likes'];
  }

  $stmt = DB::get()->prepare('SELECT `image_id`, 1 FROM `image_likes` WHERE `user_id` = ?');
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }

  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
  $res = $stmt->execute();
  $ret = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

  $GLOBALS['my_likes'] = $ret;
  return $ret;
}
