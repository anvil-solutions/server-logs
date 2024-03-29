<main class="password">
  <h2>Willkommen</h2>
  <p>
    Bitte legen sie ein Kennwort für Ihren neuen Zugang fest.
  </p>
  <form method="POST">
    <input type="hidden" name="csrf" value="<?= Session::getInstance()->getCSRFToken() ?>">
    <label for="newPassword">Kennwort</label>
    <input name="newPassword" type="password" required id="newPassword" oninput="setValidity()" autocomplete="new-password">
    <label for="newPasswordRepeat">Kennwort bestätigen</label>
    <input name="newPasswordRepeat" type="password" required id="newPasswordRepeat" oninput="setValidity()" autocomplete="new-password">
    <button type="submit" class="btn">Anmelden</button>
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
