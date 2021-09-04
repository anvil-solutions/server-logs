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
    <?php
      error_reporting(E_ALL);
      // Workaround for domains not connected to ~/
      $DOCUMENT_ROOT = preg_replace('=^([/a-z0-9]+/htdocs/).*$=','\1',getenv('DOCUMENT_ROOT'));
    ?>
    <h2>Heute</h2>
    <?php
      $filename = $DOCUMENT_ROOT.'logs/access.log.current';
      if (is_dir($filename) || !file_exists($filename)) {
        echo '<p>Es wurde keine Zugriffsdatei gefunden.</p>';
      } else {
        $clicks = array_filter(
        	file($filename),
        	function ($line) {
        		return !(strpos($line, 'js') && strpos($line, 'css'));
        	}
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
        $ips = array_map(
          function ($line) {
        		return substr($line, 0, strpos($line, ' '));
        	},
        	$clicks
        );

        echo '<p>Heute '.count($clicks).' Aufrufe von '.count(array_unique($ips)).' unterschiedlichen Geräten.</p>';
      }
    ?>
    <div id="chartTimes"></div>
    <div class="res-grid">
      <div id="chartCountryClicks"></div>
      <div id="chartCountryDevices"></div>
    </div>
    <h2>Verlauf</h2>
    <?php
      $path = $DOCUMENT_ROOT.'logs';
      $files = array_diff(scandir($path), array('.', '..'));
      $files = array_filter(
        array_diff(scandir($path), array('.', '..')),
        function ($file) {
          return strpos($file, 'access.log') > -1 && strpos($file, 'gz') > -1;
        }
      );

      $labels = array();
      $values1 = array();
      $values2 = array();
      foreach($files as $file) {
        $resource = gzopen($path.'/'.$file, 'r');
        $clicks = array_filter(
          explode('" "-"', gzread($resource, 1048576)),
          function ($line) {
            return !(strpos($line, 'js') && strpos($line, 'css'));
          }
        );
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
        array_push($values1, count($clicks));
        array_push($values2, count(array_unique($ips)));
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
      echo 'const dataTimes = { labels: '.json_encode(array_keys($times)).', datasets: [{ values: '.json_encode(array_values($times)).'}]};';
      echo 'const dataClicks = { labels: '.json_encode($labels).', datasets: [{ values: '.json_encode($values1).'}]};';
      echo 'const dataDevices = { labels: '.json_encode($labels).', datasets: [{ values: '.json_encode($values2).'}]};';
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
    new frappe.Chart("#chartClicks", {
      title: 'Klicks',
      data: dataClicks,
      type: 'line',
      colors: ['#1976D2'],
      lineOptions: options
    });
    new frappe.Chart("#chartDevices", {
      title: 'Geräte',
      data: dataDevices,
      type: 'line',
      colors: ['#1976D2'],
      lineOptions: options
    });

    const dataLoading = { labels: ['Lade', ''], datasets: [{ values: [1, 0] }] };
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

    fetch(location.origin + '/locations.php')
      .then(response => response.json())
      .then(data => {
        countryClickChart.update(data[0]);
        countryDeviceChart.update(data[1]);
      });
  </script>
</body>
</html>
