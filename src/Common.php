<?php
  // Workaround for domains not connected to ~/
  $DOCUMENT_ROOT = preg_replace('=^([/a-z0-9]+/htdocs/).*$=','\1',getenv('DOCUMENT_ROOT'));

  function isRelevantEntry($line) {
    $line = strtolower($line);
    return strlen($line) > 0
      && strpos($line, $_SERVER['HTTP_HOST']) === false
      && strpos($line, 'get') !== false
      && strpos($line, '" 2') !== false
      && strpos($line, '.js') === false
      && strpos($line, '.css') === false
      && strpos($line, '.json') === false
      && strpos($line, '.ico') === false
      && strpos($line, '.svg') === false
      && strpos($line, '.png') === false
      && strpos($line, '.jpg') === false
      && strpos($line, '.xml') === false
      && strpos($line, '.txt') === false
      && strpos($line, '.ttf') === false
      && strpos($line, '.woff') === false
      && strpos($line, '.woff2') === false
      && strpos($line, '.mp3') === false
      && strpos($line, '.mp4') === false
      && strpos($line, '.pdf') === false
      && strpos($line, '.zip') === false
      && strpos($line, '.env') === false;
  }

  function isError($line) {
    $line = strtolower($line);
    return strlen($line) > 0
      && strpos($line, $_SERVER['HTTP_HOST']) === false
      && strpos($line, 'get') !== false
      && strpos($line, '" 4') !== false;
  }

  function getIpFromLine($line) {
    return substr($line, 0, strpos($line, ' '));
  }

  function getRequestFromLine($line) {
    $offset = strpos($line, '"GET ');
    if ($offset === false) return false;
    else $offset += 5;
    return str_replace(['.html', '.php'], '', substr($line, $offset, strpos($line, ' HTTP/') - $offset));
  }

  function getUserAgentFromLine($line) {
    $offset = strposX($line, '"', 5) + 1;
    return substr($line, $offset, strposX($line, '"', 6) - $offset);
  }

  function getDateFromLine($line) {
    return substr($line, strpos($line, '[') + 1, 11);
  }

  function getTimeFromLine($line) {
    $cut = substr($line, strpos($line, '['));
    $offset = strpos($cut, ':');
    return substr($cut, $offset + 1, strpos($cut, ' ') - $offset);
  }

  function getHourFromLine($line) {
    $hour = substr($line, strpos($line, '['));
    return (int)substr($hour, strpos($hour, ':') + 1, 2);
  }

  function getReadableDate($string) {
    return substr_replace(substr_replace($string, '. ', 6, 1), '. ', 2, 1);
  }

  function strposX($haystack, $needle, $number = 1) {
    if (substr_count($haystack, $needle) < $number) return false;
    else return strpos($haystack, $needle, $number > 1
      ? strposX($haystack, $needle, $number - 1) + strlen($needle)
      : 0
    );
  }
?>
