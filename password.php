<?php

  $pageTitle = 'Passwort Ändern';
  require_once(__DIR__.'/src/layout/header.php');
  if (isset($_POST['password']) && isset($_POST['newPassword']) && isset($_POST['newPasswordRepeat'])) {
    $settings = json_decode(file_get_contents('./src/settings.json'));
    if (password_verify($_POST['password'], $settings->passwordHash) && $_POST['newPassword'] === $_POST['newPasswordRepeat']) {
      $settings->passwordHash = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
      file_put_contents('./src/settings.json', json_encode($settings));
      $_SESSION['loggedIn'] = '';
      include('./src/layout/password-changed.html');
      exit;
    }
  }
?>
<main class="password">
  <h2>Passwort Ändern</h2>
  <p>
    Bitte legen sie ein neues Kennwort fest.
  </p>
  <form method="POST">
    <label for="password">Kennwort</label>
    <input name="password" type="password" id="password" required autocomplete="current-password">
    <label for="newPassword">Neues Kennwort</label>
    <input name="newPassword" type="password" required id="newPassword" oninput="setValidity()" autocomplete="new-password">
    <label for="newPasswordRepeat">Neues Kennwort bestätigen</label>
    <input name="newPasswordRepeat" type="password" required id="newPasswordRepeat" oninput="setValidity()" autocomplete="new-password">
    <button type="submit" class="btn">Ändern</button>
  </form>
  <script>
    function setValidity() {
      const newPassword = document.getElementById('newPassword');
      const newPasswordRepeat = document.getElementById('newPasswordRepeat');
      if (newPassword.value === newPasswordRepeat.value) newPasswordRepeat.setCustomValidity('');
      else newPasswordRepeat.setCustomValidity('Stimmt nicht überein.');
    }
  </script>
</main>
