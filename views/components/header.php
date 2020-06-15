<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="stylesheet" href="/bundle/main.css">
<script>const userLogon = <?php if (user_logon()) { ?>true<?php } else { ?>false<?php } ?>;</script>
</head>
<body class="min-h-screen flex flex-col">
  <div id="header" class="bg-gray-800 text-white">
    <div class="container mx-auto flex">
      <div class="flex">
        <a href="/" class="sf-button-header">oadhesiv's Camagru</a>
        <?php display_app(); ?>
      </div>
      <div class="flex ml-auto">
        <?php display_login_logout(); ?>
      </div>
    </div>
  </div>
  <div class="container mx-auto">
    <?php display_notification(); ?>
  </div>
  <div id="main" class="flex-1">
    <div class="container mx-auto p-3">
