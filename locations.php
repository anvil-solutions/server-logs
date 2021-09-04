<?php
  error_reporting(E_ALL);
  header('Content-Type: application/json; charset=utf-8');
  // Workaround for domains not connected to ~/
  $DOCUMENT_ROOT = preg_replace('=^([/a-z0-9]+/htdocs/).*$=','\1',getenv('DOCUMENT_ROOT'));
  $filename = $DOCUMENT_ROOT.'logs/access.log.current';
  if (is_dir($filename) || !file_exists($filename)) {
    echo '[{"labels":["Lade","Fehler"],"datasets":[{"values":[0,1]}]}, {"labels":["Lade","Fehler"],"datasets":[{"values":[0,1]}]}]';
  } else {
    $ips = array_map(
      function ($line) {
        return substr($line, 0, strpos($line, ' '));
      },
      array_filter(
        file($filename),
        function ($line) {
          return !(strpos($line, 'js') && strpos($line, 'css'));
        }
      )
    );

    $locations = array();
    $locationMap = array();
    $ipClicks = array_count_values($ips);
    foreach(array_unique($ips) as $ip) {
      $country = json_decode(file_get_contents('https://geolocation-db.com/json/'.$ip))->country_code;
      array_push($locations, $country);
      isset($locationMap[$country])
        ? $locationMap[$country] += $ipClicks[$ip]
        : $locationMap[$country] = $ipClicks[$ip];
    }
    $locations = array_count_values($locations);
    arsort($locationMap);
    arsort($locations);
    echo '[{"labels":'.json_encode(array_keys($locationMap)).',"datasets":[{"values":'.json_encode(array_values($locationMap)).'}]},{"labels":'.json_encode(array_keys($locations)).',"datasets":[{"values":'.json_encode(array_values($locations)).'}]}]';
  }
?>
