<?php
  $pageTitle = 'Abgemeldet';
  require_once('./src/layout/header.php');
  $_SESSION['loggedIn'] = '';
?>
<main class="login">
  <h2>Abgemeldet</h2>
  <p>Sie wurden erfolgreich erfolgreich abgemeldet.</p>
  <p>Die Seite lädt nach fünf Sekunden automatisch neu.</p>
  <script>setTimeout(() => location = './', 5000)</script>
</main>
