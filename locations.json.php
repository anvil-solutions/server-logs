<?php
  require_once('common.php');

  $filename = $DOCUMENT_ROOT.'logs/access.log.current';
  if (is_dir($filename) || !file_exists($filename)) {
    echo '[{"labels":["Lade","Fehler"],"datasets":[{"values":[0,1]}]}, {"labels":["Lade","Fehler"],"datasets":[{"values":[0,1]}]}]';
  } else {
    $ips = array_map(
      function ($line) {
        return substr($line, 0, strpos($line, ' '));
      },
      getRelevantEntries(file($filename))
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
