<?php

  error_reporting(E_ALL);
  session_set_cookie_params(0, '/', null, true, true);
  session_name('SESSION_LOGS');
  session_start();
  $settings = json_decode(file_get_contents(__DIR__.'/settings.json'));
  $newUser = true;
  if (property_exists($settings, 'passwordHash')) {
    $newUser = false;
    if (isset($_POST['password'])) {
      if (password_verify($_POST['password'], $settings->passwordHash)) {
        $_SESSION['loggedIn'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['trys'] = 0;
      }
      else isset($_SESSION['trys']) ? $_SESSION['trys']++ : $_SESSION['trys'] = 1;
    }
  } else {
    if (isset($_POST['newPassword']) && isset($_POST['newPasswordRepeat'])) {
      if ($_POST['newPassword'] === $_POST['newPasswordRepeat']) {
        $newUser = false;
        $settings->passwordHash = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
        file_put_contents(__DIR__.'/settings.json', json_encode($settings));
        $_SESSION['loggedIn'] = $_SERVER['HTTP_USER_AGENT'];
      }
    }
  }
  $loggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === $_SERVER['HTTP_USER_AGENT'];
?>
