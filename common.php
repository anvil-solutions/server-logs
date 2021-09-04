<?php
  error_reporting(E_ALL);
  if (strpos($_SERVER['REQUEST_URI'], 'common') > -1) http_response_code(404);

  // Workaround for domains not connected to ~/
  $DOCUMENT_ROOT = preg_replace('=^([/a-z0-9]+/htdocs/).*$=','\1',getenv('DOCUMENT_ROOT'));

  function getRelevantEntries($array) {
    return array_filter(
      $array,
      function ($line) {
        return !(strpos($line, 'js') && strpos($line, 'css') && strpos($line, 'json'));
      }
    );
  }
?>
