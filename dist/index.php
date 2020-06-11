<?php

require_once __DIR__ . '/../engine/engine.php';

$action = url_get('action', '/^[a-z]+$/');

if (!$action) {
  require_once __DIR__ . '/../views/pages/index.php';
  return ;
}

switch($action) {
  case 'register':
    user_register();
    return;
  case 'login':
    user_login();
    return;
  case 'logout':
    user_logout();
    return;
  case 'setup':
    require_once __DIR__ . '/../config/' . $action . '.php';
    return;
  case 'view':
    $page = url_get('page', '/^[a-z]+$/');
    switch ($page) {
      case 'register':
      case 'login':
        require_once __DIR__ . '/../views/pages/' . $page . '.php';
        return;
      default:
        ft_reset();
        return;
    }
}
