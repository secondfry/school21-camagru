<?php

require_once __DIR__ . '/../components/header.php';

?>
  <h1 class="text-3xl my-3 font-bold">Recover your account</h1>
  <form method="post" action="/?action=recover_initiate" class="bg-white rounded" id="register-form">
    <div class="mb-4 flex flex-col md:flex-row md:flex md:items-center">
      <label class="font-bold block md:mr-3 md:w-1/5" for="email">
        Email
      </label>
      <input class="sf-input" id="email" name="email" type="email" placeholder="Email">
    </div>
    <div class="flex items-center justify-between">
      <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
        Proceed
      </button>
    </div>
  </form>
<?php

require_once __DIR__ . '/../components/footer.php';
