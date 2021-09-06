<?php
  require_once('./src/Session.php');
  if ($loggedIn === false) {
    http_response_code(403);
    exit;
  }

  require_once('./src/Common.php');
  $filename = $DOCUMENT_ROOT.'logs/access.log.current';
  if (is_dir($filename) || !file_exists($filename)) {
    echo '[{"labels":["Fehler"],"datasets":[{"values":[1]}]}, {"labels":["Fehler"],"datasets":[{"values":[1]}]}]';
  } else {
    $file = file($filename);
    $devicesPerLocation = [];
    $clicksPerLocation = [];
    $deviceLocations = [];
    foreach ($file as $line) {
      if (isRelevantEntry($line)) {
        $ip = getIpFromLine($line);
        if (!isset($deviceLocations[$ip])) {
          $country = json_decode(file_get_contents('https://geolocation-db.com/json/'.$ip))->country_code;
          if ($country === null || $country === '' || $country === 'Not found') $deviceLocations[$ip] = '?';
          else $deviceLocations[$ip] = $country;
          isset($devicesPerLocation[$deviceLocations[$ip]])
            ? $devicesPerLocation[$deviceLocations[$ip]]++
            : $devicesPerLocation[$deviceLocations[$ip]] = 1;
        }
        isset($clicksPerLocation[$deviceLocations[$ip]])
          ? $clicksPerLocation[$deviceLocations[$ip]]++
          : $clicksPerLocation[$deviceLocations[$ip]] = 1;
      }
    }
    arsort($devicesPerLocation);
    arsort($clicksPerLocation);
    echo '[{"labels":'.json_encode(array_keys($clicksPerLocation)).',"datasets":[{"values":'.json_encode(array_values($clicksPerLocation)).'}]},{"labels":'.json_encode(array_keys($devicesPerLocation)).',"datasets":[{"values":'.json_encode(array_values($devicesPerLocation)).'}]}]';
  }
?>
