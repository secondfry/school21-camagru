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
       COUNT(`il`.`id`) AS `likes`,
       COUNT(`ic`.`id`) AS `comments`,
       CASE WHEN `ilm`.`user_id` = ? THEN 1 ELSE 0 END AS `liked`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_likes` `ilm` ON `images`.`id` = `ilm`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
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
  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
  $stmt->bindValue(2, $page * 16, PDO::PARAM_INT);
  $res = $stmt->execute();
  return $stmt;
}

function get_most_liked() {
  $stmt = DB::get()->prepare(
    '
SELECT `images`.*,
       COUNT(`il`.`id`) AS `likes`,
       COUNT(`ic`.`id`) AS `comments`,
       CASE WHEN `ilm`.`user_id` = ? THEN 1 ELSE 0 END AS `liked`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_likes` `ilm` ON `images`.`id` = `ilm`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
GROUP BY `images`.`id`
HAVING COUNT(`il`.`id`) > 0
ORDER BY `likes` DESC
LIMIT 8'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
  $res = $stmt->execute();
  return $stmt;
}

function get_most_commented() {
  $stmt = DB::get()->prepare(
    '
SELECT `images`.*,
       COUNT(`il`.`id`) AS `likes`,
       COUNT(`ic`.`id`) AS `comments`,
       CASE WHEN `ilm`.`user_id` = ? THEN 1 ELSE 0 END AS `liked`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_likes` `ilm` ON `images`.`id` = `ilm`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
GROUP BY `images`.`id`
HAVING `comments` > 0
ORDER BY `comments` DESC, `created` DESC
LIMIT 4'
  );
  if (!$stmt) {
    $_SESSION['notification'][] = [
      'text' => 'Ошибка SQL.',
      'type' => 'bad',
    ];
    ft_reset();
  }
  $stmt->bindValue(1, $_SESSION['user']['id'] ?? 0, PDO::PARAM_INT);
  $res = $stmt->execute();
  return $stmt;
}

function get_user_images(int $id) {
  $stmt = DB::get()->prepare(
    '
SELECT `images`.*,
       COUNT(`il`.`id`) AS `likes`,
       COUNT(`ic`.`id`) AS `comments`,
       CASE WHEN `ilm`.`user_id` = ? THEN 1 ELSE 0 END AS `liked`
FROM `images`
LEFT JOIN `image_likes` `il` ON `images`.`id` = `il`.`image_id`
LEFT JOIN `image_likes` `ilm` ON `images`.`id` = `ilm`.`image_id`
LEFT JOIN `image_comments` `ic` ON `images`.`id` = `ic`.`image_id`
WHERE `images`.`user_id` = ?
GROUP BY `images`.`id`
ORDER BY `comments` DESC, `created` DESC
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
  $stmt->bindValue(2, $id, PDO::PARAM_INT);
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
  $i = 0;
  ?>
  <div class="sf-thumbnails">
    <?php
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      ?>
      <div class="sf-image">
        <a href="/?action=view&page=image&id=<?= $row['id'] ?>">
          <img src="<?= $row['path'] ?>"/>
        </a>
        <div class="sf-image-info">
          <a class="sf-image-icon <?php if ($row['liked']) echo 'sf-image-likes-solid-icon'; else echo 'sf-image-likes-icon'; ?> sf-action-like" href="#" data-id="<?= $row['id'] ?>"></a><span><?= $row['likes'] ?></span>
          <span class="sf-image-icon sf-image-comments-icon"></span><span><?= $row['comments'] ?></span>
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
