<?php require_once(__DIR__.'/../Session.php'); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Anvil</title>
  <link rel="icon" href="./favicon.ico">
  <link rel="stylesheet" type="text/css" href="./main.min.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>history.replaceState(null, null, window.location.href);</script>
</head>
<body>
  <header>
    <h1>Anvil Solutions</h1>
  </header>
  <?php
    if ($loggedIn === false) {
      $settings = json_decode(file_get_contents(__DIR__.'/../settings.json'));
      if (isset($_POST['password']) && password_verify($_POST['password'], $settings->passwordHash)) {
        $_SESSION['loggedIn'] = $_SERVER['HTTP_USER_AGENT'];
      } else {
        isset($_SESSION['trys']) ? $_SESSION['trys']++ : $_SESSION['trys'] = 1;
        if ($_SESSION['trys'] > 5) {
          http_response_code(418);
          include('locked.html');
        } else {
          include('login.html');
          // Use the line below to get a new hash
          // echo '<p hidden>'.$_POST['password'].' '.password_hash($_POST['password'], PASSWORD_DEFAULT).'</p>';
        }
        exit;
      }
    }
    require_once(__DIR__.'/../Common.php');
  ?>
