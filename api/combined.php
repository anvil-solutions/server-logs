<?php

  require_once(__DIR__.'/../src/Session.php');
  require_once(__DIR__.'/../src/Common.php');
  require_once(__DIR__.'/../src/BrowserDetection.php');
  $_BROWSER = new foroco\BrowserDetection();

  function parseZippedLogs(string $path, array $files): array {
    global $_BROWSER;
    $clicksPerDay = [];
    $devicesPerDay = [];
    $fileDateMap = [];
    $operatingSystems = [];
    $browsers = [];
    $successPages = [];
    $errorPages = [];
    foreach ($files as $filename) {
      $file = '';
      $resource = gzopen($path.'/'.$filename, 'r');
      while (!gzeof($resource)) $file .= gzread($resource, 4096);
      gzclose($resource);
      $file = explode(PHP_EOL, $file);
      $clicks = 0;
      $devices = [];
      $fileDateMap[$filename] = [];
      $currentDate = getDateFromLine($file[0]);
      foreach ($file as $line) {
        if (isRelevantEntry($line)) {
          $date = getDateFromLine($line);
          if ($date !== $currentDate) {
            $clicksPerDay[$currentDate] = $clicks;
            $devicesPerDay[$currentDate] = count($devices);
            array_push($fileDateMap[$filename], $currentDate);
            $currentDate = $date;
            $clicks = 0;
            $devices = [];
          }
          $clicks++;
          $ip = getIpFromLine($line);
          if (!in_array($ip, $devices)) {
            array_push($devices, $ip);
            $browserData = $_BROWSER->getAll(getUserAgentFromLine($line));
            countUpValue($operatingSystems, $browserData['os_name']);
            countUpValue($browsers, $browserData['browser_name']);
          }
          $request = getRequestFromLine($line);
          if ($request !== false) countUpValue($successPages, $request);
        } else if (isError($line)) {
          $request = getRequestFromLine($line);
          if ($request !== false) countUpValue($errorPages, $request);
        }
      }
      $clicksPerDay[$currentDate] = $clicks;
      $devicesPerDay[$currentDate] = count($devices);
      array_push($fileDateMap[$filename], $currentDate);
    }
    arsort($operatingSystems);
    arsort($browsers);
    arsort($successPages);
    arsort($errorPages);
    return [
      'averageClicksPerDay' => round(array_sum($clicksPerDay) / count($clicksPerDay), 2),
      'averageDevicesPerDay' => round(array_sum($devicesPerDay) / count($devicesPerDay), 2),
      'clicksPerDay' => $clicksPerDay,
      'devicesPerDay' => $devicesPerDay,
      'fileDateMap' => $fileDateMap,
      'operatingSystems' => $operatingSystems,
      'browsers' => $browsers,
      'successPages' => $successPages,
      'errorPages' => $errorPages
    ];
  }

  header('Content-Type: application/json; charset=utf-8');
  if ($loggedIn === false) {
    http_response_code(401);
    echo '{}';
    exit;
  }

  $path = $DOCUMENT_ROOT.'logs';
  $files = array_filter(
    array_diff(scandir($path), array('.', '..')),
    function ($file) {
      return strpos($file, 'access.log') > -1 && strpos($file, 'gz') > -1;
    }
  );
  echo json_encode(parseZippedLogs($path, $files));
?>
