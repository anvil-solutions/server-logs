<?php

  $pageTitle = 'Details';
  require_once(__DIR__.'/src/layout/header.php');
  require_once(__DIR__.'/src/BrowserDetection.php');
  $_BROWSER = new foroco\BrowserDetection();

  if (!isset($_GET['i']) || !isset($_GET['j'])) {
    echo '{}';
    exit;
  }
  $filename = $DOCUMENT_ROOT.'logs/'.$_GET['i'];
  if (
    is_dir($filename)
    || !file_exists($filename)
    || strpos($filename, '..') !== false
    || strpos($filename, 'access.log') === false
    || preg_match('/^(([1-9])|([0][1-9])|([1-2][0-9])|([3][0-1]))\/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\/\d{4}$/', $_GET['j']) === 0
  ) {
    echo '{}';
    exit;
  }

  $file = '';
  $resource = gzopen($filename, 'r');
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
    if (getDateFromLine($line) === $_GET['j']) {
      if (isRelevantEntry($line)) {
        $clicks++;
        $ip = getIpFromLine($line);
        if (!isset($devices[$ip])) {
          $browserData = $_BROWSER->getAll(getUserAgentFromLine($line));
          isset($operatingSystems[$browserData['os_name']])
            ? $operatingSystems[$browserData['os_name']]++
            : $operatingSystems[$browserData['os_name']] = 1;
          isset($browsers[$browserData['browser_name']])
            ? $browsers[$browserData['browser_name']]++
            : $browsers[$browserData['browser_name']] = 1;
          $devices[$ip]['operatingSystem'] = $browserData['os_name'];
          $devices[$ip]['browser'] = $browserData['browser_name'];
        }
        $clicksPerHour[getHourFromLine($line)]++;
        $request = getRequestFromLine($line);
        if ($request !== false) {
          isset($successPages[$request])
            ? $successPages[$request]++
            : $successPages[$request] = 1;
          isset($devices[$ip]['requests'])
            ? array_push($devices[$ip]['requests'], [getTimeFromLine($line), $request, getHostFromLine($line)])
            : $devices[$ip]['requests'] = [[getTimeFromLine($line), $request, getHostFromLine($line)]];
        }
      } else if (isError($line)) {
        $request = getRequestFromLine($line);
        if ($request !== false) isset($errorPages[$request])
            ? $errorPages[$request]++
            : $errorPages[$request] = 1;
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
  $data = [
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
?>
<main>
  <a href="./">← Zurück zur Startseite</a>
  <h2>Übersicht für den <?= getReadableDate($_GET['j']); ?></h2>
  <p>
    Am <?= getReadableDate($_GET['j']) ?> gab es insgesamt
    <span id="clicks">...</span> Aufrufe von
    <span id="devices">...</span> unterschiedlichen Geräten. Die folgenden
    Graphen zeigen Ihnen den zeitlichen Verlauf und Geräteinformationen.
  </p>
  <div id="chartTimes" data-title="Klicks pro Stunde" data-type="line"></div>
  <div class="res-grid">
    <div id="chartOSes" data-title="Genutzte Betriebssysteme" data-type="bar"></div>
    <div id="chartBrowsers" data-title="Genutzte Browser" data-type="bar"></div>
  </div>
  <h2>Sitzungen</h2>
  <p>
    Der folgende Abschnitt beschäftigt sich mit den anonym aufgezeichneten
    Sitzungen. Zu Sehen sind die am häufigsten aufgerufenen Seiten, sowie die
    beliebtesten Einstiegs- und Ausstiegsseiten. Die durchschnittliche
    Sitzungsdauer beträgt <span id="averageSessionDuration">...</span> mit
    <span id="averageSessionClicks">...</span> Aufrufen. Die Absprungrate
    beträgt <span id="bounceRate">...</span>%.
  </p>
  <div class="res-grid">
    <div id="chartEntry" data-title="Einstiegsseiten" data-type="bar"></div>
    <div id="chartExit" data-title="Ausstiegsseiten" data-type="bar"></div>
  </div>
  <div id="chartFiles" data-title="Am häufigsten angefragt" data-type="bar"></div>
  <div id="chartErrors" data-title="Fehlerseiten" data-type="bar"></div>
  <h2>Besucher Flow</h2>
  <p>
    Es folgt eine genauere Aufschlüsselung der einzelnen Sitzungen. Sie sehen
    allgemeine Informationen zur Sitzung als auch die Reihenfolge und Uhrzeit
    der besuchten Seiten.
  </p>
  <div id="sessions"></div>
</main>
<script type="module">
  import { Chart } from 'https://unpkg.com/frappe-charts@1.6.1/dist/frappe-charts.min.esm.js';

  const data = <?= json_encode($data) ?>

  function initChart(id, data, tooltipOptions = {}, axisOptions = {}) {
    const { title, type } = document.querySelector(id).dataset;
    return new Chart(
      id,
      {
        title,
        data,
        type,
        colors: ['#1976D2'],
        lineOptions: { regionFill: 1, hideDots: 1 },
        axisOptions,
        tooltipOptions
      }
    );
  }

  function timestampToString(timestamp) {
    return new Date((timestamp - 3600) * 1000).toLocaleTimeString('de')
  }

  function addSessions(element) {
    let sessionIndex = 1;
    for (const device of data.devices) {
      const heading = document.createElement('h3');
      const paragraph = document.createElement('p');
      const timeline = document.createElement('div');
      heading.append(document.createTextNode('Sitzung ' + sessionIndex));
      paragraph.append(
        document.createTextNode('Sitzungsdauer: ' + timestampToString(device.duration)),
        document.createElement('br'),
        document.createTextNode('Seitenaufrufe: ' + device.requests.length),
        document.createElement('br'),
        document.createTextNode('Betriebssystem: ' + device.operatingSystem),
        document.createElement('br'),
        document.createTextNode('Browser: ' + device.browser)
      );
      timeline.classList.add('timeline');
      for (const flow of device.requests) {
        const timelineEntry = document.createElement('div');
        const path = document.createElement('div');
        const domain = document.createElement('div');
        const domainSmall = document.createElement('small');
        const time = document.createElement('div');
        const timeSmall = document.createElement('small');
        const separator = document.createElement('span');
        path.append(document.createTextNode(flow[1]));
        domainSmall.append(document.createTextNode(flow[2]));
        domain.append(domainSmall);
        timeSmall.append(document.createTextNode(flow[0]));
        time.append(timeSmall);
        timelineEntry.append(path, domain, time);
        separator.classList.add('separator');
        timeline.append(timelineEntry, separator);
      }
      element.append(heading, paragraph, timeline);
      sessionIndex++;
    }
  }

  document.getElementById('clicks').textContent = data.clicks;
  document.getElementById('devices').textContent = data.devices.length;
  document.getElementById('averageSessionDuration').textContent = timestampToString(data.averageSessionDuration);
  document.getElementById('averageSessionClicks').textContent = data.averageSessionClicks;
  document.getElementById('bounceRate').textContent = data.bounceRate;
  addSessions(document.getElementById('sessions'))

  initChart(
    '#chartTimes',
    {
      labels: Object.keys(data.clicksPerHour),
      datasets: [{ values: Object.values(data.clicksPerHour) }],
      yMarkers: [{ label: "Durchschnitt", value: data.averageClicksPerHour }]
    },
    {
      formatTooltipX: d => d + ' Uhr',
      formatTooltipY: d => d + ' Klicks'
    }
  );
  initChart(
    '#chartOSes',
    {
      labels: Object.keys(data.operatingSystems),
      datasets: [{ values: Object.values(data.operatingSystems) }]
    },
    { formatTooltipY: d => d + ' Geräte' },
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartBrowsers',
    {
      labels: Object.keys(data.browsers),
      datasets: [{ values: Object.values(data.browsers) }]
    },
    { formatTooltipY: d => d + ' Geräte' },
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartEntry',{
      labels: Object.keys(data.entryPages).slice(0, 5),
      datasets: [{ values: Object.values(data.entryPages).slice(0, 5) }]
    },
    {},
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartExit',{
      labels: Object.keys(data.exitPages).slice(0, 5),
      datasets: [{ values: Object.values(data.exitPages).slice(0, 5) }]
    },
    {},
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartFiles',
    {
      labels: Object.keys(data.successPages).slice(0, 5),
      datasets: [{ values: Object.values(data.successPages).slice(0, 5) }]
    },
    { formatTooltipY: d => d + ' Klicks' },
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartErrors',
    {
      labels: Object.keys(data.errorPages).slice(0, 5),
      datasets: [{ values: Object.values(data.errorPages).slice(0, 5) }]
    },
    { formatTooltipY: d => d + ' Klicks' },
    { xAxisMode: 'tick' }
  );
</script>
