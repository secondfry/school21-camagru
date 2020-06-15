<?php

require_once __DIR__ . '/../components/header.php';

$id = url_get('id', '/[0-9]+/');
if (!$id) {
  ft_reset();
}

$row = fetch_image($id);
$stmt = fetch_comments($id);

?>
  <h1 class="text-3xl my-3 font-bold">Photobomb by @<?= htmlspecialchars($row['username']) ?></h1>
  <div class="sf-image sf-image_solo">
    <img src="<?= $row['path'] ?>"/>
    <div class="sf-image-info">
      <a class="sf-image-icon <?php if ($row['liked']) echo 'sf-image-likes-solid-icon'; else echo 'sf-image-likes-icon'; ?> sf-action-like" href="#" data-id="<?= $row['id'] ?>"></a>
      <span class="sf-counter"><?= $row['likes'] ?></span>
      <span class="sf-image-icon sf-image-comments-icon"></span>
      <span class="sf-counter"><?= $row['comments'] ?></span>
      <span class="sf-username <?=get_color($row['user_id'])?>"><?= htmlentities($row['username']) ?></span>
      <?php if ($row['user_id'] === $_SESSION['user']['id']) { ?><a class="sf-remove text-red-700" href="#" data-id="<?=$row['id']?>">Delete this forever?</a><?php } ?>
    </div>
  </div>
  <div class="sf-comments">
    <?php
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        ?>
        <div class="sf-comment">
          <div class="sf-comment__username <?=get_color($row['user_id'])?>"><?= htmlentities($row['username']) ?></div>
          <div class="sf-comment__text"><?= htmlentities($row['text']) ?></div>
        </div>
        <?php
      }
    ?>
  </div>
  <form method="post" action="/?action=comment">
    <div class="sf-comment-input-wrap">
      <input type="hidden" name="id" value="<?=$id?>">
      <input type="text" name="comment" id="comment" placeholder="Enter your comment, comrade">
      <input type="submit" id="submit" value="send">
    </div>
  </form>
<?php

require_once __DIR__ . '/../components/footer.php';
