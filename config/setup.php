<?php

require_once __DIR__ . '/../engine/engine.php';

$data = file_get_contents(__DIR__ . '/camagru.sqlite.sql');

$res = DB::get()->exec($data);

header('Location: /');
