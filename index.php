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
      // Do some sanity checks
      $filename = $DOCUMENT_ROOT.'logs/access.log.current';

      if (is_dir($filename) || !file_exists($filename)) {
        echo '<p>Es wurde keine Zugriffsdatei gefunden.</p>';
      } else {
        $content = file($filename);
        $clicks = array_filter(
        	$content,
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
        echo '<p>Heute '.count($clicks).' Aufrufe von '.count(array_unique($ips)).' unterschiedlichen Geräten.</p>';
      }
    ?>
    <h2>Traffic</h2>
    <p>
      Unten sehen Sie eine Tabelle mit Aufrufszahlen und Menge der transferierten Daten in den einzelnen Monaten des laufenden Jahres.
    </p>
    <div class="table-container">
    <?php
      // Do some sanity checks
      $filename = $DOCUMENT_ROOT.'logs/traffic.html/index.html';

      if (is_dir($filename) || !file_exists($filename)) {
        echo '<p>Es wurden keine Traffic-Daten gefunden.</p>';
      } else {
        $content = implode('', file($filename));
        $doc = new DOMDocument();
        $doc->loadHTML($content);
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
</body>
</html>
