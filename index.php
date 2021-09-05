<?php require_once('./src/Common.php'); ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Anvil</title>
  <link rel="icon" href="./favicon.ico">
  <link rel="stylesheet" type="text/css" href="./main.min.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
  <header>
    <h1>Anvil Solutions</h1>
  </header>
  <main>
    <h2>Heute</h2>
    <?php
      $filename = $DOCUMENT_ROOT.'logs/access.log.current';
      if (is_dir($filename) || !file_exists($filename)) {
        echo '<p>Es wurde keine Zugriffsdatei gefunden.</p>';
      } else {
        $clicks = getRelevantEntries(file($filename));

        $ips = array_map(
          function ($line) {
        		return substr($line, 0, strpos($line, ' '));
        	},
        	$clicks
        );

        $times = array_map(
          function ($line) {
            $offset = strpos($line, '[') + 1;
            $date = substr($line, $offset, strpos($line, ']') - $offset);
            $offset = strpos($date, ':') + 1;
        		return substr($date, $offset, 2);
        	},
        	$clicks
        );
        $times = array_count_values($times);

        echo '<p>Heute insgesamt '.count($clicks).' Aufrufe von '.count(array_unique($ips)).' unterschiedlichen Geräten.</p>';

        require_once('./src/BrowserDetection.php');
        $_BROWSER = new foroco\BrowserDetection();
        $ipStore = array();
        $osMap = array();
        $browserMap = array();
        foreach ($clicks as $line) {
          $ip = substr($line, 0, strpos($line, ' '));
          if (!in_array($ip, $ipStore)) {
            $offset = strposX($line, '"', 5) + 1;
            $browserData = $_BROWSER->getAll(substr($line, $offset, strposX($line, '"', 6) - $offset));
            isset($osMap[$browserData['os_name']])
              ? $osMap[$browserData['os_name']]++
              : $osMap[$browserData['os_name']] = 1;
            isset($browserMap[$browserData['browser_name']])
              ? $browserMap[$browserData['browser_name']]++
              : $browserMap[$browserData['browser_name']] = 1;
            array_push($ipStore, $ip);
          }
        }
        arsort($osMap);
        arsort($browserMap);
      }
    ?>
    <div id="chartTimes"></div>
    <div class="res-grid">
      <div id="chartCountryClicks"></div>
      <div id="chartCountryDevices"></div>
      <div id="chartOSes"></div>
      <div id="chartBrowsers"></div>
    </div>
    <h2>Verlauf</h2>
    <?php
      $path = $DOCUMENT_ROOT.'logs';
      $files = array_filter(
        array_diff(scandir($path), array('.', '..')),
        function ($file) {
          return strpos($file, 'access.log') > -1 && strpos($file, 'gz') > -1;
        }
      );

      $labels = array();
      $dataClicks = array();
      $dataDevices = array();
      foreach ($files as $file) {
        $resource = gzopen($path.'/'.$file, 'r');
        $clicks = getRelevantEntries(explode(PHP_EOL, gzread($resource, 1048576)));
        $ips = array_map(
          function ($line) {
            return substr($line, 0, strpos($line, ' '));
          },
          $clicks
        );
        $date = str_replace(
          array('.1', '.2', '.3', '.4', '.5', '.6', '.7'),
          array(' Mo',' Di',' Mi',' Do',' Fr',' Sa',' So',),
          substr($file, 11, strpos($file, '.gz') - 11)
        );
        array_push($labels, 'KW '.$date);
        array_push($dataClicks, count($clicks));
        array_push($dataDevices, count(array_unique($ips)));
        gzclose($resource);
      }
    ?>
    <div id="chartClicks"></div>
    <div id="chartDevices"></div>
    <h2>Traffic</h2>
    <p>
      Unten sehen Sie eine Tabelle mit Aufrufszahlen und Menge der transferierten Daten in den einzelnen Monaten des laufenden Jahres.
    </p>
    <div class="table-container">
    <?php
      $filename = $DOCUMENT_ROOT.'logs/traffic.html/index.html';

      if (is_dir($filename) || !file_exists($filename)) {
        echo '<p>Es wurden keine Traffic-Daten gefunden.</p>';
      } else {
        $doc = new DOMDocument();
        $doc->loadHTML(implode('', file($filename)));
        echo str_replace(
          'Megabytes',
          'MB',
          preg_replace('#<a.*?>(.*?)</a>#i', '\1', $doc->saveHTML($doc->getElementsByTagName('table')->item(0)))
        );
      }
    ?>
    </div>
    <h2>Umwandlungstabelle</h2>
    <p>
      1 kB = 1000 Bytes<br>
      1 MB = 1000 kB<br>
      1 GB = 1000 MB
    </p>
  </main>
  <script src="https://unpkg.com/frappe-charts@1.2.4/dist/frappe-charts.min.iife.js"></script>
  <script>
    <?php
      echo 'const dataTimes = { labels: '.json_encode(array_keys($times)).', datasets: [{ values: '.json_encode(array_values($times)).'}] };';
      echo 'const dataOSes = { labels: '.json_encode(array_keys($osMap)).', datasets: [{ values: '.json_encode(array_values($osMap)).'}] };';
      echo 'const dataBrowsers = { labels: '.json_encode(array_keys($browserMap)).', datasets: [{ values: '.json_encode(array_values($browserMap)).'}] };';
      echo 'const dataClicks = { labels: '.json_encode($labels).', datasets: [{ values: '.json_encode($dataClicks).'}] };';
      echo 'const dataDevices = { labels: '.json_encode($labels).', datasets: [{ values: '.json_encode($dataDevices).'}] };';
    ?>
    const options = {
      regionFill: 1,
      hideDots: 1
    }
    new frappe.Chart("#chartTimes", {
      title: 'Klicks pro Stunde',
      data: dataTimes,
      type: 'line',
      colors: ['#1976D2'],
      lineOptions: options
    });
    new frappe.Chart("#chartOSes", {
      title: 'Genutzte Betriebssysteme',
      data: dataOSes,
      type: 'bar',
      colors: ['#1976D2']
    });
    new frappe.Chart("#chartBrowsers", {
      title: 'Genutzte Browser',
      data: dataBrowsers,
      type: 'bar',
      colors: ['#1976D2']
    });
    new frappe.Chart("#chartClicks", {
      title: 'Klicks pro Tag',
      data: dataClicks,
      type: 'line',
      colors: ['#1976D2'],
      lineOptions: options
    });
    new frappe.Chart("#chartDevices", {
      title: 'Geräte pro Tag',
      data: dataDevices,
      type: 'line',
      colors: ['#1976D2'],
      lineOptions: options
    });

    const dataLoading = { labels: ['Lädt'], datasets: [{ values: [0] }] };
    const countryClickChart = new frappe.Chart("#chartCountryClicks", {
      title: 'Klicks pro Land',
      data: dataLoading,
      type: 'bar',
      colors: ['#1976D2']
    });
    const countryDeviceChart = new frappe.Chart("#chartCountryDevices", {
      title: 'Geräte pro Land',
      data: dataLoading,
      type: 'bar',
      colors: ['#1976D2']
    });

    fetch('./locations.json')
      .then(response => response.json())
      .then(data => {
        countryClickChart.update(data[0]);
        countryDeviceChart.update(data[1]);
      })
      .catch(() => {
        const dataError = { labels: ['Fehler'], datasets: [{ values: [1] }] };
        countryClickChart.update(dataError);
        countryDeviceChart.update(dataError);
      });

    history.replaceState(null, null, window.location.href);
  </script>
</body>
</html>
