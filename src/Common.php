<?php
  error_reporting(E_ALL);
  $_passwordhash = '$2y$10$/oornTLB8QWBr0Osb6xdNuybzW3KAOtzEUy0yUK1nCqVgDeP8qs4G';

  session_start();
  if (isset($_POST['password'])) {
    if (password_verify($_POST['password'], $_passwordhash)) {
      $_SESSION['loggedIn'] = 1;
    }
  }
  if (!isset($_SESSION['loggedIn'])) {
    include('login.html');
    // Use the line below to get a new hash
    // echo '<p hidden>'.$_POST['password'].' '.password_hash($_POST['password'], PASSWORD_DEFAULT).'</p>';
    exit;
  }

  // Workaround for domains not connected to ~/
  $DOCUMENT_ROOT = preg_replace('=^([/a-z0-9]+/htdocs/).*$=','\1',getenv('DOCUMENT_ROOT'));

  function isRelevantEntry($line) {
    return strpos($line, '.js') === false
      && strpos($line, '.css') === false
      && strpos($line, '.json') === false
      && strpos($line, '.ico') === false
      && strpos($line, '.svg') === false
      && strpos($line, '.png') === false
      && strpos($line, '.jpg') === false;
  }

  function getRelevantEntries($array) {
    return array_filter(
      $array,
      'isRelevantEntry'
    );
  }

  function strposX($haystack, $needle, $number = 0) {
    return strpos($haystack, $needle,
      $number > 1 ?
      strposX($haystack, $needle, $number - 1) + strlen($needle) : 0
    );
  }
?>