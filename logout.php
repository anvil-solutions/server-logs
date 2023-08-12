<?php

  $pageTitle = 'Abgemeldet';
  require_once(__DIR__.'/src/layout/header.php');
  Session::getInstance()->logout();
?>
<main class="login">
  <h2>Abgemeldet</h2>
  <p>Sie wurden erfolgreich abgemeldet.</p>
  <script>location = './'</script>
</main>
