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
    check_uuid('confirmations');
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
  case 'upload':
    ft_reset_no_auth();
    image_upload();
    return;
  case 'like_toggle':
    ft_reset_no_auth();
    like_toggle();
    return;
  case 'comment':
    ft_reset_no_auth();
    perform_comment();
    return;
  case 'change_username':
    ft_reset_no_auth();
    change_username();
    return;
  case 'change_email':
    ft_reset_no_auth();
    change_email();
    return;
  case 'change_password':
    ft_reset_no_auth();
    change_password();
    return;
  case 'change_notification':
    ft_reset_no_auth();
    change_notification();
    return;
  case 'remove':
    ft_reset_no_auth();
    image_remove();
    return;
  case 'view':
    $page = url_get('page', '/^[a-z0-9_]+$/');
    switch ($page) {
      case 'create':
      case 'user':
        ft_reset_no_auth();
        require_once __DIR__ . '/../views/pages/' . $page . '.php';
        return;
      case 'register':
      case 'login':
      case 'recover_step_1':
      case 'index':
      case 'image':
        require_once __DIR__ . '/../views/pages/' . $page . '.php';
        return;
      case 'recover_step_2':
        check_uuid('recovers');
        require_once __DIR__ . '/../views/pages/' . $page . '.php';
        return;
      default:
        ft_reset();
        return;
    }
    break;
  default:
    ft_reset();
    return;
}
