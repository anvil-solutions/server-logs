<?php

  $pageTitle = 'Abgemeldet';
  require_once('./src/layout/header.php');
  $_SESSION['loggedIn'] = '';
?>
<main class="login">
  <h2>Abgemeldet</h2>
  <p>Sie wurden erfolgreich erfolgreich abgemeldet.</p>
  <script>location = './'</script>
</main>
