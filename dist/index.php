<?php

require_once __DIR__ . '/../engine/engine.php';

$action = url_get('action', '/^[a-z_]+$/');

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
  case 'confirm':
    user_confirm();
    return;
  case 'recover_initiate':
    user_recover_initiate();
    return;
  case 'recover_perform':
    user_recover_perform();
    return;
  case 'setup':
    require_once __DIR__ . '/../config/' . $action . '.php';
    return;
  case 'view':
    $page = url_get('page', '/^[a-z0-9_]+$/');
    switch ($page) {
      case 'register':
      case 'login':
      case 'recover_step_1':
      case 'recover_step_2':
      case 'create':
        require_once __DIR__ . '/../views/pages/' . $page . '.php';
        return;
      default:
        ft_reset();
        return;
    }
}
