<main class="login">
  <h2>Willkommen</h2>
  <p>
    Bitte melden Sie sich mit Ihrem Kennwort an.
  </p>
  <form method="POST">
    <input type="hidden" name="csrf" value="<?= Session::getInstance()->getCSRFToken() ?>">
    <input name="password" type="password" placeholder="Kennwort" aria-label="Kennwort" required  autocomplete="current-password">
    <button type="submit" class="btn">Anmelden</button>
  </form>
</main>
