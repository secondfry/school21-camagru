<?php

require_once __DIR__ . '/../components/header.php';

?>
<h1 class="text-2xl">Login</h1>
<form method="post" action="/?action=login" class="bg-white rounded">
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="username">
      Username
    </label>
    <input class="sf-input" id="username" name="name" type="text" placeholder="Username">
  </div>
  <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
    <label class="font-bold block md:mr-3 md:w-1/5" for="password">
      Password
    </label>
    <input class="sf-input" id="password" name="passwd" type="password" placeholder="******************">
  </div>
  <div class="flex items-center justify-between">
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
      Login
    </button>
    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="/?action=view&page=recover_step_1">
      Forgot Password?
    </a>
  </div>
</form>
<?php

require_once __DIR__ . '/../components/footer.php';
