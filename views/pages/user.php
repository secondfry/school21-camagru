<?php

require_once __DIR__ . '/../components/header.php';

$stmt = DB::get()->query('SELECT * FROM `users` WHERE `id` = ' . $_SESSION['user']['id']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<h1 class="text-3xl my-3 font-bold">User Panel</h1>

<h2 class="text-2xl my-3 font-bold">Notifications</h2>
<form method="post" action="/?action=change_notification" class="bg-white rounded" id="notification-form">
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <input id="notification" name="notification" type="checkbox" <?php if ($row['notification'] === '1') { ?>checked="checked"<?php } ?>>
    <label class="font-bold" class="flex-auto" for="notification"> Receive new comments to your posts? </label>
  </div>
  <div class="flex items-center justify-between">
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
      Submit
    </button>
  </div>
</form>

<h2 class="text-2xl my-3 font-bold">Change Username</h2>
<form method="post" action="/?action=change_username" class="bg-white rounded" id="username-form">
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="cu_username">
      New username
    </label>
    <input class="sf-input" id="cu_username" name="name" type="text" placeholder="<?=htmlentities($row['username'])?>">
  </div>
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="cu_password">
      Old password
    </label>
    <input class="sf-input" id="cu_password" name="passwd" type="password" placeholder="******************">
  </div>
  <div class="flex items-center justify-between">
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
      Change username
    </button>
  </div>
</form>

<h2 class="text-2xl my-3 font-bold">Change Email</h2>
<form method="post" action="/?action=change_email" class="bg-white rounded" id="email-form">
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="ce_email">
      New email
    </label>
    <input class="sf-input" id="ce_email" name="email" type="email" placeholder="<?=htmlentities($row['email'])?>">
  </div>
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="ce_password">
      Old password
    </label>
    <input class="sf-input" id="ce_password" name="passwd" type="password" placeholder="******************">
  </div>
  <div class="flex items-center justify-between">
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
      Change email
    </button>
  </div>
</form>

<h2 class="text-2xl my-3 font-bold">Change Password</h2>
<form method="post" action="/?action=change_password" class="bg-white rounded" id="password-form">
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="cp_password_new">
      New password<br>
      <span class="font-normal font-smaller">At least 8 symbols. Must have uppercase letter.</span>
    </label>
    <input class="sf-input" id="cp_password_new" name="new_passwd" type="password" placeholder="******************">
  </div>
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="cp_password_old">
      Old password
    </label>
    <input class="sf-input" id="cp_password_old" name="old_passwd" type="password" placeholder="******************">
  </div>
  <div class="flex items-center justify-between">
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
      Change password
    </button>
  </div>
</form>
<?php

require_once __DIR__ . '/../components/footer.php';
