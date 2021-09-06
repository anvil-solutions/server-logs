<?php
  error_reporting(E_ALL);
  session_name('SESSION');
  session_cache_expire(30);
  session_start();
  if (isset($_POST['password'])) {
    $settings = json_decode(file_get_contents(__DIR__.'/settings.json'));
    if (password_verify($_POST['password'], $settings->passwordHash)) $_SESSION['loggedIn'] = $_SERVER['HTTP_USER_AGENT'];
    else isset($_SESSION['trys']) ? $_SESSION['trys']++ : $_SESSION['trys'] = 1;
  }
  $loggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === $_SERVER['HTTP_USER_AGENT'];
?>
