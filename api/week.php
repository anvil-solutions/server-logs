<?php

  require_once(__DIR__.'/../src/Session.php');
  require_once(__DIR__.'/../src/Common.php');
  require_once(__DIR__.'/../src/BrowserDetection.php');
  $_BROWSER = new foroco\BrowserDetection();

  function parseLogs(string $path): array {
    if (is_dir($path) || !file_exists($path)) return [];

    global $_BROWSER;
    $file = '';
    $resource = gzopen($path, 'r');
    while (!gzeof($resource)) $file .= gzread($resource, 4096);
    gzclose($resource);
    $file = explode(PHP_EOL, $file);
    $clicks = 0;
    $devices = [];
    $clicksPerDay = [];
    $operatingSystems = [];
    $browsers = [];
    $successPages = [];
    $errorPages = [];
    foreach ($file as $line) {
      if (isRelevantEntry($line)) {
        $clicks++;
        $ip = getIpFromLine($line);
        if (!isset($devices[$ip])) {
          $browserData = $_BROWSER->getAll(getUserAgentFromLine($line));
          countUpValue($operatingSystems, $browserData['os_name']);
          countUpValue($browsers, $browserData['browser_name']);
        }
        countUpValue($clicksPerDay, getDateFromLine($line));
        $request = getRequestFromLine($line);
        if ($request !== false) {
          countUpValue(
            $successPages,
            $request.' ― '.getHostFromLine($line)
          );
          isset($devices[$ip]['requests'])
            ? array_push($devices[$ip]['requests'], [getTimeFromLine($line), $request, getHostFromLine($line)])
            : $devices[$ip]['requests'] = [[getTimeFromLine($line), $request, getHostFromLine($line)]];
        }
      } else if (isError($line)) {
        $request = getRequestFromLine($line);
        if ($request !== false) countUpValue(
          $errorPages,
          $request.' ― '.getHostFromLine($line)
        );
      }
    }
    arsort($operatingSystems);
    arsort($browsers);
    arsort($successPages);
    arsort($errorPages);
    uasort($devices, function ($a, $b) {
      return (count($b['requests']) - count($a['requests']));
    });

    $entryPages = [];
    $exitPages = [];
    $bouncedSessions = 0;
    foreach ($devices as $key => $device) {
      countUpValue(
        $entryPages,
        $device['requests'][0][1].' ― '.$device['requests'][0][2]
      );
      countUpValue(
        $exitPages,
        array_slice($device['requests'], -1)[0][1].' ― '.array_slice($device['requests'], -1)[0][2]
      );
      $devices[$key]['duration'] = strtotime(array_slice($device['requests'], -1)[0][0]) - strtotime($device['requests'][0][0]);
      if (count($device['requests']) === 1) $bouncedSessions++;
    }
    arsort($entryPages);
    arsort($exitPages);

    $sessionDatasetSize = count($devices);
    return [
      'clicks' => $clicks,
      'devices' => count($devices),
      'clicksPerDay' => $clicksPerDay,
      'operatingSystems' => $operatingSystems,
      'browsers' => $browsers,
      'successPages' => $successPages,
      'errorPages' => $errorPages,
      'entryPages' => $entryPages,
      'exitPages' => $exitPages,
      'bounceRate' => $sessionDatasetSize === 0 ? 0 : round($bouncedSessions / $sessionDatasetSize * 100),
      'averageClicksPerDay' => array_sum($clicksPerDay) / max(1, count($clicksPerDay)),
      'averageSessionDuration' => $sessionDatasetSize === 0 ? 0 : (int) (array_sum(array_column($devices, 'duration')) / $sessionDatasetSize),
      'averageSessionClicks' => $sessionDatasetSize === 0 ? 0 : round(array_sum(array_map('count', array_column($devices, 'requests'))) / $sessionDatasetSize, 2)
    ];
  }

  header('Content-Type: application/json; charset=utf-8');
  if (!Session::getInstance()->isLoggedIn()) {
    http_response_code(401);
    echo '{}';
    exit;
  }

  if (
    !isset($_GET['file'])
    || strpos($_GET['file'], '..') !== false
    || strpos($_GET['file'], 'access.log') === false
  ) {
    http_response_code(400);
    echo '{}';
    exit;
  }

  echo json_encode(parseLogs($DOCUMENT_ROOT.'logs/'.$_GET['file']));
?>
