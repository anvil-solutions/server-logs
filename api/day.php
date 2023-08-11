<?php

  require_once(__DIR__.'/../src/Session.php');
  require_once(__DIR__.'/../src/Common.php');
  require_once(__DIR__.'/../src/BrowserDetection.php');
  $_BROWSER = new foroco\BrowserDetection();

  function parseLogs(string $path, string $date): array {
    if (is_dir($path) || !file_exists($path)) return [];

    global $_BROWSER;
    $file = '';
    $resource = gzopen($path, 'r');
    while (!gzeof($resource)) $file .= gzread($resource, 4096);
    gzclose($resource);
    $file = explode(PHP_EOL, $file);
    $clicks = 0;
    $devices = [];
    $clicksPerHour = array_fill(0, 24, 0);
    $operatingSystems = [];
    $browsers = [];
    $successPages = [];
    $errorPages = [];
    foreach ($file as $line) {
      if (getDateFromLine($line) === $date) {
        if (isRelevantEntry($line)) {
          $clicks++;
          $ip = getIpFromLine($line);
          if (!isset($devices[$ip])) {
            $browserData = $_BROWSER->getAll(getUserAgentFromLine($line));
            countUpValue($operatingSystems, $browserData['os_name']);
            countUpValue($browsers, $browserData['browser_name']);
            $devices[$ip]['operatingSystem'] = $browserData['os_name'];
            $devices[$ip]['browser'] = $browserData['browser_name'];
          }
          $clicksPerHour[getHourFromLine($line)]++;
          $request = getRequestFromLine($line);
          if ($request !== false) {
            countUpValue($successPages, $request);
            isset($devices[$ip]['requests'])
              ? array_push($devices[$ip]['requests'], [getTimeFromLine($line), $request, getHostFromLine($line)])
              : $devices[$ip]['requests'] = [[getTimeFromLine($line), $request, getHostFromLine($line)]];
          }
        } else if (isError($line)) {
          $request = getRequestFromLine($line);
          if ($request !== false) countUpValue($errorPages, $request);
        }
      }
    }
    arsort($operatingSystems);
    arsort($browsers);
    arsort($successPages);
    arsort($errorPages);
    uasort($devices, function ($a, $b) {
      return (count($b['requests']) - count($a['requests']));
    });

    $sessionData = [];
    $bouncedSessions = 0;
    foreach ($devices as $key => $device) {
      $sessionData[$key] = [];
      array_push($sessionData[$key], $device['requests'][0][1]);
      array_push($sessionData[$key], array_slice($device['requests'], -1)[0][1]);
      $devices[$key]['duration'] = strtotime(array_slice($device['requests'], -1)[0][0]) - strtotime($device['requests'][0][0]);
      if (count($device['requests']) === 1) $bouncedSessions++;
    }

    $entryPages = array_count_values(array_column($sessionData, 0));
    $exitPages = array_count_values(array_column($sessionData, 1));
    arsort($entryPages);
    arsort($exitPages);

    $sessionDatasetSize = count($devices);
    return [
      'clicks' => $clicks,
      'devices' => array_values($devices),
      'clicksPerHour' => $clicksPerHour,
      'operatingSystems' => $operatingSystems,
      'browsers' => $browsers,
      'successPages' => $successPages,
      'errorPages' => $errorPages,
      'entryPages' => $entryPages,
      'exitPages' => $exitPages,
      'bounceRate' => $sessionDatasetSize === 0 ? 0 : round($bouncedSessions / $sessionDatasetSize * 100),
      'averageClicksPerHour' => array_sum($clicksPerHour) / count($clicksPerHour),
      'averageSessionDuration' => $sessionDatasetSize === 0 ? 0 : (int) (array_sum(array_column($devices, 'duration')) / $sessionDatasetSize),
      'averageSessionClicks' => $sessionDatasetSize === 0 ? 0 : round(array_sum(array_map('count', array_column($devices, 'requests'))) / $sessionDatasetSize, 2)
    ];
  }

  header('Content-Type: application/json; charset=utf-8');
  if ($loggedIn === false) {
    http_response_code(401);
    echo '{}';
    exit;
  }

  if (
    !isset($_GET['file'])
    || !isset($_GET['date'])
    || strpos($_GET['file'], '..') !== false
    || strpos($_GET['file'], 'access.log') === false
    || preg_match('/^(([1-9])|([0][1-9])|([1-2][0-9])|([3][0-1]))\/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\/\d{4}$/', $_GET['date']) === 0
  ) {
    http_response_code(400);
    echo '{}';
    exit;
  }

  echo json_encode(
    parseLogs($DOCUMENT_ROOT.'logs/'.$_GET['file'], $_GET['date'])
  );
?>
