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
<main class="login">
  <a href="./">← Zurück zur Startseite</a>
  <h2>Passwort Ändern</h2>
  <p>
    Bitte legen sie ein neues Kennwort fest.
  </p>
  <form method="POST">
    <label><div>Kennwort: </div><input name="password" type="password" required></label>
    <label><div>Neues Kennwort: </div><input name="newPassword" type="password" required id="password1" oninput="setValidity()"></label>
    <label><div>Neues Kennwort bestätigen: </div><input name="newPasswordRepeat" type="password" required id="password2" oninput="setValidity()"></label>
    <div class="btnContainer"><button type="submit" class="btn">Ändern</button></div>
  </form>
  <script>
    function setValidity() {
      const password1 = document.getElementById('password1');
      const password2 = document.getElementById('password2');
      if (password1.value === password2.value) password2.setCustomValidity('');
      else password2.setCustomValidity('Stimmt nicht überein.');
    }
  </script>
</main>
