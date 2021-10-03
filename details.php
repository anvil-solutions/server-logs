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
    if (is_dir($filename) || !file_exists($filename) || $_GET['j'] === '0' || strpos($filename, '..') !== false) {
      echo '<p>Es wurde kein Zugriffsprotokoll gefunden.</p>';
      exit;
    } else {
      require_once('./src/BrowserDetection.php');
      $_BROWSER = new foroco\BrowserDetection();
      $file = '';
      $resource = gzopen($filename, 'r');
      while (!gzeof($resource)) $file .= gzread($resource, 4096);
      gzclose($resource);
      $file = explode(PHP_EOL, $file);
      $clicks = 0;
      $deviceMap = [];
      $clicksPerHour = array_fill(0, 24, 0);
      $osMap = [];
      $browserMap = [];
      $fileMap = [];
      foreach ($file as $line) {
        if (getDateFromLine($line) === $_GET['j'] && isRelevantEntry($line)) {
          $clicks++;
          $ip = getIpFromLine($line);
          if (!isset($deviceMap[$ip])) {
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
          if ($request !== false) {
            isset($fileMap[$request])
              ? $fileMap[$request]++
              : $fileMap[$request] = 1;
            isset($deviceMap[$ip])
              ? array_push($deviceMap[$ip], [getTimeFromLine($line), $request])
              : $deviceMap[$ip] = [[getTimeFromLine($line), $request]];
          }
        }
      }
      arsort($osMap);
      arsort($browserMap);
      arsort($fileMap);
      array_splice($fileMap, 5);
      usort($deviceMap, function ($a, $b) {
        return (count($b) - count($a));
      });

      $sessionData = [];
      foreach ($deviceMap as $key => $user) {
        array_push($sessionData, []);
        array_push($sessionData[$key], strtotime(array_slice($user, -1)[0][0]) - strtotime($user[0][0]));
        array_push($sessionData[$key], count($user));
        array_push($sessionData[$key], $user[0][1]);
        array_push($sessionData[$key], array_slice($user, -1)[0][1]);
      }

      $entryMap = array_count_values(array_column($sessionData, 2));
      $exitMap = array_count_values(array_column($sessionData, 3));
      arsort($entryMap);
      arsort($exitMap);
      array_splice($entryMap, 5);
      array_splice($exitMap, 5);

      echo '<p>Am '.getReadableDate($_GET['j']).' gab es insgesamt '.$clicks.' Aufrufe von '.count($deviceMap).' unterschiedlichen Geräten. Die folgenden Graphen zeigen Ihnen den zeitlichen Verlauf und Geräteinformationen.</p>';
    }
  ?>
  <div id="chartTimes" data-title="Klicks pro Stunde" data-type="line"></div>
  <div class="res-grid">
    <div id="chartOSes" data-title="Genutzte Betriebssysteme" data-type="bar"></div>
    <div id="chartBrowsers" data-title="Genutzte Browser" data-type="bar"></div>
  </div>
  <h2>Sitzungen</h2>
  <p>
    Der folgende Abschnitt beschäftigt sich mit den anonym aufgezeichneten Sitzungen.
    Zu Sehen sind die am häufigsten aufgerufenen Seiten, sowie die beliebtesten Einstiegs- und Ausstiegsseiten.
    <?php
      $datasetSize = count($sessionData);
      echo 'Die durchschnittliche Sitzungsdauer beträgt '
        .gmdate("H:i:s", array_sum(array_column($sessionData, 0)) / $datasetSize).' mit '
        .round(array_sum(array_column($sessionData, 1)) / $datasetSize, 2).' Aufrufen.';
    ?>
  </p>
  <div class="res-grid">
    <div id="chartEntry" data-title="Einstiegsseiten" data-type="bar"></div>
    <div id="chartExit" data-title="Ausstiegsseiten" data-type="bar"></div>
  </div>
  <div id="chartFiles" data-title="Am häufigsten angefragt" data-type="bar"></div>
  <h2>Besucher Flow</h2>
  <p>
    Es folgt eine genauere Aufschlüsselung der einzelnen Sitzungen.
    Sie sehen allgemeine Informationen zur Sitzung als auch die Reihenfolge und Uhrzeit der besuchten Seiten.
  </p>
  <?php
    $i = 1;
    foreach ($deviceMap as $key => $user) {
      echo '<h3>Sitzung '.$i.'</h3>';
      echo '<p>Sitzungsdauer: '.gmdate("H:i:s", $sessionData[$key][0]).'<br>Seitenaufrufe: '.$sessionData[$key][1].'</p>';
      echo '<div class="timeline">';
      foreach ($user as $flow) {
        echo '<div><div>'.$flow[1].'</div><small>'.$flow[0].' Uhr</small></div><span class="separator"></span>';
      }
      echo '</div>';
      $i++;
    }
  ?>
</main>
<script src="https://unpkg.com/frappe-charts@1.2.4/dist/frappe-charts.min.iife.js"></script>
<script>
  <?php
    echo 'const dataTimes = { labels: '.json_encode(array_keys($clicksPerHour)).', datasets: [{ values: '.json_encode(array_values($clicksPerHour)).'}], yMarkers: [{ label: "Durchschnitt", value: '.(array_sum($clicksPerHour) / count($clicksPerHour)).' }] };';
    echo 'const dataOSes = { labels: '.json_encode(array_keys($osMap)).', datasets: [{ values: '.json_encode(array_values($osMap)).'}] };';
    echo 'const dataBrowsers = { labels: '.json_encode(array_keys($browserMap)).', datasets: [{ values: '.json_encode(array_values($browserMap)).'}] };';
    echo 'const dataEntry = { labels: '.json_encode(array_keys($entryMap)).', datasets: [{ values: '.json_encode(array_values($entryMap)).'}] };';
    echo 'const dataExit = { labels: '.json_encode(array_keys($exitMap)).', datasets: [{ values: '.json_encode(array_values($exitMap)).'}] };';
    echo 'const dataFiles = { labels: '.json_encode(array_keys($fileMap)).', datasets: [{ values: '.json_encode(array_values($fileMap)).'}] };';
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
  initChart('#chartEntry', dataEntry, {}, { xAxisMode: 'tick' });
  initChart('#chartExit', dataExit, {}, { xAxisMode: 'tick' });
  initChart('#chartFiles', dataFiles, {
    formatTooltipY: d => d + ' Klicks'
  }, { xAxisMode: 'tick' });
</script>
