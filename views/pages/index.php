<?php

require_once __DIR__ . '/../components/header.php';

?>
<h1 class="text-2xl">Gallery</h1>
<h2 class="text-xl">Most liked</h2>
<?php

$stmt = get_most_liked();
if ($stmt->rowCount())
  display_query_thumbnails($stmt);
else {
  ?>
  You'd better like something and it will instantly get to the top!
  <?php
}

?>
<h2 class="text-xl">Most commented</h2>
<?php

$stmt = get_most_commented();
if ($stmt->rowCount())
  display_query_thumbnails($stmt);
else {
  ?>
  You'd better comment upon something and it will instantly get to the top!
  <?php
}

?>
<h2 class="text-xl">Recent additions</h2>
<?php

$stmt = get_all_images(0);
display_query_thumbnails($stmt);

?>
<?php

require_once __DIR__ . '/../components/footer.php';
