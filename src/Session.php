<?php
  error_reporting(E_ALL);
  session_name('SESSION');
  session_cache_expire(30);
  session_start();
  $loggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === $_SERVER['HTTP_USER_AGENT'];
?>
