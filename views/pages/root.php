<?php

require_once __DIR__ . '/../components/header.php';

?>
<h1 class="text-2xl">Gallery</h1>
<h2 class="text-xl">Most liked</h2>
<?php

$res = DB::get()->exec('SELECT * FROM images');
var_dump($res);

?>
<h2 class="text-xl">Most commented</h2>
<?php

$res = DB::get()->exec('SELECT * FROM images');
var_dump($res);

?>
<h2 class="text-xl">Recent additions</h2>
<?php

$res = DB::get()->exec('SELECT * FROM images');
var_dump($res);

?>
<?php

require_once __DIR__ . '/../components/footer.php';
