<?php

require_once __DIR__ . '/../engine/engine.php';

if (empty($_GET['action'])) {
  require_once __DIR__ . '/../views/pages/root.php';
  return ;
}

switch($_GET['action']) {
  case 'setup':
    require_once __DIR__ . '/../config/setup.php';
    break;
}
