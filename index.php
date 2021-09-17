<?php
  $pageTitle = 'Übersicht';
  require_once('./src/layout/header.php');
?>
<main>
  <h2>Willkommen</h2>
  <p>
    Willkommen auf Ihrer Übersichtsseite.
    Mit Schließen des Browsers werden Sie automatisch abgemeldet.
    Zuletzt aktualisiert: <?php echo date('H:i:s'); ?> Uhr.
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
  <div id="chartTimes" data-title="Klicks pro Stunde" data-type="line"></div>
  <div class="res-grid">
    <div id="chartCountryClicks" data-title="Klicks pro Land" data-type="bar"></div>
    <div id="chartCountryDevices" data-title="Geräte pro Land" data-type="bar"></div>
    <div id="chartOSes" data-title="Genutzte Betriebssysteme" data-type="bar"></div>
    <div id="chartBrowsers" data-title="Genutzte Browser" data-type="bar"></div>
  </div>
  <div id="chartFiles" data-title="Am Häufigsten angefragt" data-type="bar"></div>
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
  <div id="chartClicks" data-title="Klicks pro Tag" data-type="line"></div>
  <div id="chartDevices" data-title="Geräte pro Tag" data-type="line"></div>
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
  function initChart(id, data, tooltipOptions = {}, axisOptions = {}) {
    const dataset = document.querySelector(id).dataset;
    return new frappe.Chart(id, {
      title: dataset.title,
      data: data,
      type: dataset.type,
      colors: ['#1976D2'],
      lineOptions: { regionFill: 1, hideDots: 1 },
      axisOptions: axisOptions,
      tooltipOptions: tooltipOptions
    });
  }

  initChart('#chartTimes', dataTimes, {
    formatTooltipX: d => d + ' Uhr',
    formatTooltipY: d => d + ' Klicks'
  });
  initChart('#chartOSes', dataOSes, {
    formatTooltipY: d => d + ' Geräte'
  }, { xAxisMode: 'tick' });
  initChart('#chartBrowsers', dataBrowsers, {
    formatTooltipY: d => d + ' Geräte'
  }, { xAxisMode: 'tick' });
  initChart('#chartFiles', dataFiles, {
    formatTooltipY: d => d + ' Klicks'
  }, { xAxisMode: 'tick' });
  initChart('#chartClicks', dataClicks, {
    formatTooltipY: d => d + ' Klicks'
  }, { xIsSeries: true });
  initChart('#chartDevices', dataDevices, {
    formatTooltipY: d => d + ' Geräte'
  }, { xIsSeries: true });

  const regionConverter = new Intl.DisplayNames(['de'], { type: 'region' });
  const dataLoading = { labels: ['Lädt', ''], datasets: [{ values: [1, 0] }] };
  const dataError = { labels: ['', 'Fehler'], datasets: [{ values: [0, 1] }] };
  function convertRegion(d) {
    try {
      return regionConverter.of(d);
    } catch (e) {
      return 'Unbekannt';
    }
  }
  const countryClickChart = initChart('#chartCountryClicks', dataLoading, {
    formatTooltipX: convertRegion,
    formatTooltipY: d => d + ' Klicks'
  }, { xAxisMode: 'tick' });
  const countryDeviceChart = initChart('#chartCountryDevices', dataLoading, {
    formatTooltipX: convertRegion,
    formatTooltipY: d => d + ' Geräte'
  }, { xAxisMode: 'tick' });

  fetch('./locations.json')
    .then(response => response.json())
    .then(data => {
      countryClickChart.update(data[0]);
      countryDeviceChart.update(data[1]);
    })
    .catch(() => {
      countryClickChart.update(dataError);
      countryDeviceChart.update(dataError);
    });
</script>
