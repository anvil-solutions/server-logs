<?php
  // Workaround for domains not connected to ~/
  $DOCUMENT_ROOT = preg_replace('=^([/a-z0-9]+/htdocs/).*$=','\1',getenv('DOCUMENT_ROOT'));

  function isRelevantEntry($line) {
    $line = strtolower($line);
    return strpos($line, $_SERVER['HTTP_HOST']) === false
      && strpos($line, '" 404') === false
      && strpos($line, '.js') === false
      && strpos($line, '.css') === false
      && strpos($line, '.json') === false
      && strpos($line, '.ico') === false
      && strpos($line, '.svg') === false
      && strpos($line, '.png') === false
      && strpos($line, '.jpg') === false
      && strpos($line, '.xml') === false
      && strpos($line, '.txt') === false
      && strpos($line, '.env') === false;
  }

  function getRelevantEntries($array) {
    return array_filter(
      $array,
      'isRelevantEntry'
    );
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

  function getHourFromLine($line) {
    $hour = substr($line, strpos($line, '['));
    return substr($hour, strpos($hour, ':') + 1, 2);
  }

  function getReadableDate($string) {
    return str_replace(
      ['.1', '.2', '.3', '.4', '.5', '.6', '.7'],
      [' Mo',' Di',' Mi',' Do',' Fr',' Sa',' So'],
      $string
    );
  }

  function strposX($haystack, $needle, $number = 1) {
    if (substr_count($haystack, $needle) < $number) return false;
    else return strpos($haystack, $needle, $number > 1
      ? strposX($haystack, $needle, $number - 1) + strlen($needle)
      : 0
    );
  }
?>
