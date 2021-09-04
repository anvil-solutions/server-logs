<?php
  if (strpos($_SERVER['REQUEST_URI'], 'login') > -1) {
    http_response_code(404);
    exit;
  }
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Anvil</title>
  <link rel="icon" href="./favicon.ico">
  <link rel="stylesheet" type="text/css" href="./main.min.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <header>
    <h1>Anvil Solutions</h1>
  </header>
  <main class="login">
    <h2>Willkommen</h2>
    <p>
      Bitte melden Sie sich mit Ihrem Kennwort an.
    </p>
    <form method="POST">
      <label><div>Kennwort: </div><input name="password" type="password"></label>
      <div class="btnContainer"><button type="submit" class="btn">Anmelden</button></div>
    </form>
  </main>
</body>
</html>
