<?php
  require_once('./src/Common.php');
  if (!isset($_GET['i'])) $_GET['i'] = '0';
?>
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
    <a href="./">← Zurück zur Startseite</a>
    <h2>Übersicht für "KW <?php echo getReadableDate($_GET['i']); ?>"</h2>
    <?php
      $filename = $DOCUMENT_ROOT.'logs/access.log.'.(isset($_GET['i']) ? $_GET['i'] : '0').'.gz';
      if (is_dir($filename) || !file_exists($filename)) {
        echo '<p>Es wurde kein Zugriffsprotokoll gefunden.</p>';
      } else {
        require_once('./src/BrowserDetection.php');
        $_BROWSER = new foroco\BrowserDetection();
        $resource = gzopen($filename, 'r');
        $file = explode(PHP_EOL, gzread($resource, 1048576));
        $clicks = 0;
        $devices = [];
        $clicksPerHour = [];
        $osMap = [];
        $browserMap = [];
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
        		$hour = getHourFromLine($line);
            isset($clicksPerHour[$hour])
              ? $clicksPerHour[$hour]++
              : $clicksPerHour[$hour] = 1;
          }
        }
        gzclose($resource);
        arsort($osMap);
        arsort($browserMap);

        echo '<p>Für "KW '.getReadableDate($_GET['i']).'" gab es insgesamt '.$clicks.' Aufrufe von '.count($devices).' unterschiedlichen Geräten.</p>';
      }
    ?>
    <div id="chartTimes"></div>
    <div class="res-grid">
      <div id="chartOSes"></div>
      <div id="chartBrowsers"></div>
    </div>
  </main>
  <script src="https://unpkg.com/frappe-charts@1.2.4/dist/frappe-charts.min.iife.js"></script>
  <script>
    <?php
      echo 'const dataTimes = { labels: '.json_encode(array_keys($clicksPerHour)).', datasets: [{ values: '.json_encode(array_values($clicksPerHour)).'}] };';
      echo 'const dataOSes = { labels: '.json_encode(array_keys($osMap)).', datasets: [{ values: '.json_encode(array_values($osMap)).'}] };';
      echo 'const dataBrowsers = { labels: '.json_encode(array_keys($browserMap)).', datasets: [{ values: '.json_encode(array_values($browserMap)).'}] };';
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

    history.replaceState(null, null, window.location.href);
  </script>
</body>
</html>
