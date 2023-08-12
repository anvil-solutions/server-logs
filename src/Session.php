<?php

  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  error_reporting(E_ALL);

  require_once __DIR__.'/Settings.php';
  require_once __DIR__.'/SessionSingleton.php';

  Session::getInstance();

  $newUser = true;
  if (Settings::getInstance()->isSetUp()) {
    $newUser = false;
    if (isset($_POST['password']) && isset($_POST['csrf'])) {
      Session::getInstance()->login($_POST['password'], $_POST['csrf']);
    }
  } else {
    if (isset($_POST['newPassword']) && isset($_POST['newPasswordRepeat']) && isset($_POST['csrf'])) {
      if ($_POST['newPassword'] === $_POST['newPasswordRepeat']) {
        $newUser = false;
        Settings::getInstance()->setPasswordHash(password_hash($_POST['newPassword'], PASSWORD_DEFAULT));
        Session::getInstance()->login($_POST['newPassword'], $_POST['csrf']);
      }
    }
  }
?>
