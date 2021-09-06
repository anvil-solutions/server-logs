<?php
  $pageTitle = 'Übersicht';
  require_once('./src/layout/header.php');
?>
<main>
  <h2>Willkommen</h2>
  <p>
    Willkommen auf Ihrer Übersichtsseite. Mit Schließen des Browsers werden Sie automatisch abgemeldet.
  </p>
  <h2>Heutige Analyse</h2>
  <?php
    $filename = $DOCUMENT_ROOT.'logs/access.log.current';
    if (is_dir($filename) || !file_exists($filename)) {
      echo '<p>Es wurde kein Zugriffsprotokoll gefunden.</p>';
    } else {
      require_once('./src/BrowserDetection.php');
      $_BROWSER = new foroco\BrowserDetection();
      $file = file($filename);
      $clicks = 0;
      $devices = [];
      $clicksPerHour = array_fill(0, (int)date('G') + 1, 0);
      $osMap = [];
      $browserMap = [];
      $fileMap = [];
      foreach ($file as $line) {
        if (isRelevantEntry($line)) {
          $clicks++;
          $ip = getIpFromLine($line);
          if (!in_array($ip, $devices)) {
            array_push($devices, $ip);
            $browserData = $_BROWSER->getAll(getUserAgentFromLine($line));
            isset($osMap[$browserData['os_name']])
              ? $osMap[$browserData['os_name']]++
              : $osMap[$browserData['os_name']] = 1;
            isset($browserMap[$browserData['browser_name']])
              ? $browserMap[$browserData['browser_name']]++
              : $browserMap[$browserData['browser_name']] = 1;
          }
          $clicksPerHour[getHourFromLine($line)]++;
        	$request = getRequestFromLine($line);
          if ($request !== false) isset($fileMap[$request])
            ? $fileMap[$request]++
            : $fileMap[$request] = 1;
        }
      }
      arsort($osMap);
      arsort($browserMap);
      arsort($fileMap);
      array_splice($fileMap, 5);

      echo '<p>Heute gab es insgesamt '.$clicks.' Aufrufe von '.count($devices).' unterschiedlichen Geräten.</p>';
    }
  ?>
  <div id="chartTimes"></div>
  <div class="res-grid">
    <div id="chartCountryClicks"></div>
    <div id="chartCountryDevices"></div>
    <div id="chartOSes"></div>
    <div id="chartBrowsers"></div>
  </div>
  <div id="chartFiles"></div>
  <?php
    $path = $DOCUMENT_ROOT.'logs';
    $files = array_filter(
      array_diff(scandir($path), array('.', '..')),
      function ($file) {
        return strpos($file, 'access.log') > -1 && strpos($file, 'gz') > -1;
      }
    );

    $labels = [];
    $dataClicks = [];
    $dataDevices = [];
    $fileDateMap = [];
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
            array_push($labels, $currentDate);
            array_push($dataClicks, $clicks);
            array_push($dataDevices, count($devices));
            array_push($fileDateMap[$filename], $currentDate);
            $currentDate = $date;
            $clicks = 0;
            $devices = [];
          }
          $clicks++;
          $ip = getIpFromLine($line);
          if (!in_array($ip, $devices)) array_push($devices, $ip);
        }
      }
      array_push($labels, $currentDate);
      array_push($dataClicks, $clicks);
      array_push($dataDevices, count($devices));
      array_push($fileDateMap[$filename], $currentDate);
    }
  ?>
  <h2>Detailansicht</h2>
  <p>Klicken Sie auf die einzelnen Tage um eine Detailansicht des jeweiligen Datums zu erhalten.</p>
  <div class="week-grid">
    <?php
      foreach (array_reverse($fileDateMap) as $key => $file) {
        foreach (array_reverse($file) as $date) {
          echo '<a href="./details?i='.$key.'&j='.$date.'">'.$date.'</a>';
        }
      }
    ?>
  </div>
  <h2>Verlauf</h2>
  <p>
    Die folgenden Graphen zeigen Ihnen die Anzahl an Geräten und Klicks pro Tag für die aufgezeichnete Zeitspanne.
  </p>
  <div id="chartClicks"></div>
  <div id="chartDevices"></div>
  <h2>Monatliche Analyse</h2>
  <p>
    Unten sehen Sie eine Tabelle mit Aufrufszahlen und Menge der transferierten Daten in den einzelnen Monaten des laufenden Jahres.
  </p>
  <div class="table-container">
  <?php
    $filename = $DOCUMENT_ROOT.'logs/traffic.html/index.html';

    if (is_dir($filename) || !file_exists($filename)) {
      echo '<p>Es wurde kein Zugriffsprotokoll gefunden.</p>';
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
  <div class="res-grid">
    <div>
      <h2>Umwandlungstabelle</h2>
      <p>
        1 kB = 1000 Bytes<br>
        1 MB = 1000 kB<br>
        1 GB = 1000 MB
      </p>
    </div>
    <div>
      <h2>Einstellungen</h2>
      <ul>
        <li><a href="./password">Passwort ändern</a></li>
        <li><a href="./logout">Abmelden</a></li>
      </ul>
    </div>
  </div>
</main>
<script src="https://unpkg.com/frappe-charts@1.2.4/dist/frappe-charts.min.iife.js"></script>
<script>
  <?php
    echo 'const dataTimes = { labels: '.json_encode(array_keys($clicksPerHour)).', datasets: [{ values: '.json_encode(array_values($clicksPerHour)).'}], yMarkers: [{ label: "Durchschnitt", value: '.(array_sum($clicksPerHour) / count($clicksPerHour)).' }] };';
    echo 'const dataOSes = { labels: '.json_encode(array_keys($osMap)).', datasets: [{ values: '.json_encode(array_values($osMap)).'}] };';
    echo 'const dataBrowsers = { labels: '.json_encode(array_keys($browserMap)).', datasets: [{ values: '.json_encode(array_values($browserMap)).'}] };';
    echo 'const dataFiles = { labels: '.json_encode(array_keys($fileMap)).', datasets: [{ values: '.json_encode(array_values($fileMap)).'}] };';
    echo 'const dataClicks = { labels: '.json_encode($labels).', datasets: [{ values: '.json_encode($dataClicks).'}], yMarkers: [{ label: "Durchschnitt", value: '.(array_sum($dataClicks) / count($dataClicks)).' }] };';
    echo 'const dataDevices = { labels: '.json_encode($labels).', datasets: [{ values: '.json_encode($dataDevices).'}], yMarkers: [{ label: "Durchschnitt", value: '.(array_sum($dataDevices) / count($dataDevices)).' }] };';
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
    lineOptions: options,
    tooltipOptions: {
      formatTooltipX: d => d + ' Uhr',
      formatTooltipY: d => d + ' Klicks'
    }
  });
  new frappe.Chart("#chartOSes", {
    title: 'Genutzte Betriebssysteme',
    data: dataOSes,
    type: 'bar',
    colors: ['#1976D2'],
    axisOptions: {
      xAxisMode: 'tick'
    },
    tooltipOptions: {
      formatTooltipY: d => d + ' Geräte'
    }
  });
  new frappe.Chart("#chartBrowsers", {
    title: 'Genutzte Browser',
    data: dataBrowsers,
    type: 'bar',
    colors: ['#1976D2'],
    axisOptions: {
      xAxisMode: 'tick'
    },
    tooltipOptions: {
      formatTooltipY: d => d + ' Geräte'
    }
  });
  new frappe.Chart("#chartFiles", {
    title: 'Am Häufigsten angefragt',
    data: dataFiles,
    type: 'bar',
    colors: ['#1976D2'],
    axisOptions: {
      xAxisMode: 'tick'
    },
    tooltipOptions: {
      formatTooltipY: d => d + ' Anfragen'
    }
  });
  new frappe.Chart("#chartClicks", {
    title: 'Klicks pro Tag',
    data: dataClicks,
    type: 'line',
    colors: ['#1976D2'],
    lineOptions: options,
    axisOptions: {
      xIsSeries: true
    },
    tooltipOptions: {
      formatTooltipY: d => d + ' Klicks'
    }
  });
  new frappe.Chart("#chartDevices", {
    title: 'Geräte pro Tag',
    data: dataDevices,
    type: 'line',
    colors: ['#1976D2'],
    lineOptions: options,
    axisOptions: {
      xIsSeries: true
    },
    tooltipOptions: {
      formatTooltipY: d => d + ' Geräte'
    }
  });

  const regionConverter = new Intl.DisplayNames(['de'], { type: 'region' });
  function convertRegion(d) {
    try {
      return regionConverter.of(d);
    } catch (e) {
      return 'Unbekannt';
    }
  }
  const dataLoading = { labels: ['Lädt'], datasets: [{ values: [0] }] };
  const countryClickChart = new frappe.Chart("#chartCountryClicks", {
    title: 'Klicks pro Land',
    data: dataLoading,
    type: 'bar',
    colors: ['#1976D2'],
    axisOptions: {
      xAxisMode: 'tick'
    },
    tooltipOptions: {
      formatTooltipX: convertRegion,
      formatTooltipY: d => d + ' Klicks'
    }
  });
  const countryDeviceChart = new frappe.Chart("#chartCountryDevices", {
    title: 'Geräte pro Land',
    data: dataLoading,
    type: 'bar',
    colors: ['#1976D2'],
    axisOptions: {
      xAxisMode: 'tick'
    },
    tooltipOptions: {
      formatTooltipX: convertRegion,
      formatTooltipY: d => d + ' Geräte'
    }
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
</script>
