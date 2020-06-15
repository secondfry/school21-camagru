<?php

function url_get($name, $regex) {
  if (empty($_GET[$name])) {
    return false;
  }

  $status = preg_match($regex, $_GET[$name], $matches);
  if (!$status) {
    ft_reset();
  }

  return $matches[0];
}

function ft_reset() {
  ft_reset_to('/');
}

function ft_reset_to($page) {
  if ($_SERVER['REQUEST_URI'] === $page) {
    return;
  }

  header('Location: ' . $page);
  exit;
}

function display_notification() {
  if (empty($_SESSION['notification'])) {
    return;
  }

  foreach($_SESSION['notification'] as $v) {
    ?>
    <div class="notification <?=$v['type'] ?? ''?>">
      <?=$v['text'] ?? ''?>
    </div>
    <?php
  }

  $_SESSION['notification'] = [];
}

function display_login_logout() {
  $id = $_SESSION['user']['id'] ?? 0;

  if (!$id) {
    ?>
    <a href="/?action=view&page=register" class="sf-button-header">Register</a>
    <a href="/?action=view&page=login" class="sf-button-header">Login</a>
    <?php
  } else {
    ?>
    <a href="/index.php?action=logout" class="sf-button-header">Logout</a>
    <?php
  }
}

function display_app() {
  $id = $_SESSION['user']['id'] ?? 0;

  if (!$id) {
    return;
  }

  ?>
  <a href="/?action=view&page=create" class="sf-button-header">Create</a>
  <?php
}

function save_history() {
  $_SESSION['page'] = $_SERVER['REQUEST_URI'];
}

function user_logon() {
  return $_SESSION['user']['id'] !== 0;
}

function ft_reset_no_auth() {
  if (user_logon()) {
    return;
  }

  ft_reset();
}
