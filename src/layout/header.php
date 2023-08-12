<?php require_once(__DIR__.'/../Session.php'); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Anvil<?php if (isset($pageTitle)) echo ' - '.$pageTitle; ?></title>
  <link rel="icon" href="./favicon.ico">
  <link rel="stylesheet" type="text/css" href="./css/main.min.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script>history.replaceState(null, null, window.location.href);</script>
</head>
<body>
  <header>
     <a href="./" class="btn-home" title="Home">Home</a>
    <h1>Anvil Solutions</h1>
  </header>
  <?php
    if (!empty(Session::getInstance()->getWarningMessage())) {
      echo '<p class="warning-message">'.Session::getInstance()->getWarningMessage().'</p>';
    }

    if (Session::getInstance()->isLoggedIn() !== true) {
      if ($newUser) {
        require 'first.php';
      } else if (Session::getInstance()->canLogin()) {
        require 'login.php';
      } else {
        http_response_code(418);
        require 'locked.html';
      }
      exit;
    }
    require_once(__DIR__.'/../Common.php');
  ?>
