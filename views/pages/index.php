<?php

require_once __DIR__ . '/../components/header.php';

?>
<h1 class="text-3xl my-3 font-bold">Gallery</h1>
<h2 class="text-2xl my-2 font-bold">Most liked</h2>
<?php
  $stmt = get_most_liked();
  $count = display_query_thumbnails($stmt);
  if ($count === 0) { ?>
  You'd better like something and it will instantly get to the top!
  <?php }
?>
<h2 class="text-2xl my-2 font-bold">Most commented</h2>
<?php
  $stmt = get_most_commented();
  $count = display_query_thumbnails($stmt);
  if ($count === 0) { ?>
  You'd better comment upon something and it will instantly get to the top!
  <?php }
?>
<h2 class="text-2xl my-2 font-bold">Recent additions</h2>
<?php

$page = url_get('limit', '/^[0-9]+$/');
if ($page === false) {
  $page = 0;
}

$stmt = get_all_images($page);
$count = display_query_thumbnails($stmt);
$lastPage = $count < 16 ? true : false;

?>
<div class="sf-pagination">
  <?php if ($page > 1) { ?>
    <a href="/" class="bg-gray-400 hover:bg-gray-500 text-gray-800 font-bold py-2 px-4">1</a>
  <?php } ?>
  <?php if ($page > 0) { ?>
    <a href="/?action=view&page=index&limit=<?=$page - 1?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4"><?=$page?></a>
  <?php } ?>
  <span class="bg-gray-700 text-gray-200 font-bold py-2 px-4"><?=$page + 1?></span>
  <?php if (!$lastPage) { ?>
    <a href="/?action=view&page=index&limit=<?=$page + 1?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4"><?=$page + 2?></a>
  <?php } ?>
</div>
<?php

require_once __DIR__ . '/../components/footer.php';
