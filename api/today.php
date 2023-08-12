<?php

  require_once(__DIR__.'/../src/Session.php');
  require_once(__DIR__.'/../src/Common.php');

  function parseLogs(string $path): array|null {
    if (is_dir($path) || !file_exists($path)) return null;

    $file = file($path);
    $clicks = 0;
    $devices = [];
    $clicksPerHour = array_fill(0, (int)date('G') + 1, 0);
    $clicksPerFile = [];
    foreach ($file as $line) {
      if (isRelevantEntry($line)) {
        $clicks++;
        $ip = getIpFromLine($line);
        if (!in_array($ip, $devices)) {
          array_push($devices, $ip);
        }
        $clicksPerHour[getHourFromLine($line)]++;
        $request = getRequestFromLine($line);
        if ($request !== false) countUpValue($clicksPerFile, $request);
      }
    }
    arsort($clicksPerFile);
    return [
      'date' => date('d/M/Y'),
      'clicks' => $clicks,
      'devices' => count($devices),
      'clicksPerHour' => $clicksPerHour,
      'clicksPerFile' => $clicksPerFile,
      'averageClicksPerHour' => array_sum($clicksPerHour) / count($clicksPerHour)
    ];
  }

  header('Content-Type: application/json; charset=utf-8');
  if (!Session::getInstance()->isLoggedIn()) {
    http_response_code(401);
    echo '{}';
    exit;
  }

  echo json_encode(parseLogs($DOCUMENT_ROOT.'logs/access.log.current'));
?>
