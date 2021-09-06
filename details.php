<?php
  $pageTitle = 'Details';
  require_once('./src/layout/header.php');
  if (!isset($_GET['i'])) $_GET['i'] = '0';
  if (!isset($_GET['j'])) $_GET['j'] = '0';
?>
<main>
  <a href="./">← Zurück zur Startseite</a>
  <h2>Übersicht für den <?php echo getReadableDate($_GET['j']); ?></h2>
  <?php
    $filename = $DOCUMENT_ROOT.'logs/'.$_GET['i'];
    if (is_dir($filename) || !file_exists($filename) || $_GET['j'] === '0') {
      echo '<p>Es wurde kein Zugriffsprotokoll gefunden.</p>';
    } else {
      require_once('./src/BrowserDetection.php');
      $_BROWSER = new foroco\BrowserDetection();
      $file = '';
      $resource = gzopen($filename, 'r');
      while (!gzeof($resource)) $file .= gzread($resource, 4096);
      gzclose($resource);
      $file = explode(PHP_EOL, $file);
      $clicks = 0;
      $devices = [];
      $clicksPerHour = [];
      $osMap = [];
      $browserMap = [];
      $fileMap = [];
      foreach ($file as $line) {
        if (getDateFromLine($line) === $_GET['j'] && isRelevantEntry($line)) {
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
      		$hour = getHourFromLine($line);
          isset($clicksPerHour[$hour])
            ? $clicksPerHour[$hour]++
            : $clicksPerHour[$hour] = 1;
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

      echo '<p>Am '.getReadableDate($_GET['j']).' gab es insgesamt '.$clicks.' Aufrufe von '.count($devices).' unterschiedlichen Geräten.</p>';
    }
  ?>
  <div id="chartTimes"></div>
  <div class="res-grid">
    <div id="chartOSes"></div>
    <div id="chartBrowsers"></div>
  </div>
  <div id="chartFiles"></div>
</main>
<script src="https://unpkg.com/frappe-charts@1.2.4/dist/frappe-charts.min.iife.js"></script>
<script>
  <?php
    echo 'const dataTimes = { labels: '.json_encode(array_keys($clicksPerHour)).', datasets: [{ values: '.json_encode(array_values($clicksPerHour)).'}] };';
    echo 'const dataOSes = { labels: '.json_encode(array_keys($osMap)).', datasets: [{ values: '.json_encode(array_values($osMap)).'}] };';
    echo 'const dataBrowsers = { labels: '.json_encode(array_keys($browserMap)).', datasets: [{ values: '.json_encode(array_values($browserMap)).'}] };';
    echo 'const dataFiles = { labels: '.json_encode(array_keys($fileMap)).', datasets: [{ values: '.json_encode(array_values($fileMap)).'}] };';
  ?>
  new frappe.Chart("#chartTimes", {
    title: 'Klicks pro Stunde',
    data: dataTimes,
    type: 'line',
    colors: ['#1976D2'],
    lineOptions: {
      regionFill: 1,
      hideDots: 1
    },
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
      formatTooltipY: d => d + ' Klicks'
    }
  });
</script>
