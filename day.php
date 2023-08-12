<?php

  $pageTitle = 'Details';
  require_once(__DIR__.'/src/layout/header.php');
?>
<main>
  <h2>
    Übersicht für den
    <?= isset($_GET['date']) ? getReadableDate($_GET['date']) : '...' ?>
  </h2>
  <p>
    Am <?= isset($_GET['date']) ? getReadableDate($_GET['date']) : '...' ?> gab
    es insgesamt <span id="clicks">...</span> Aufrufe von
    <span id="devices">...</span> verschiedenen Geräten. Die folgenden Graphen
    zeigen Ihnen den zeitlichen Verlauf und Informationen zu den Geräten.
  </p>
  <div id="chartClicksPerHour" data-title="Klicks pro Stunde" data-type="line"></div>
  <div class="res-grid">
    <div id="chartOperatingSystems" data-title="Genutzte Betriebssysteme" data-type="bar"></div>
    <div id="chartBrowsers" data-title="Genutzte Browser" data-type="bar"></div>
  </div>
  <h2>Sitzungen</h2>
  <p>
    Der folgende Abschnitt beschäftigt sich mit den anonym erfassten
    Sitzungen. Zu Sehen sind die am häufigsten aufgerufenen Seiten, sowie die
    beliebtesten Einstiegs- und Ausstiegsseiten. Die durchschnittliche
    Sitzungsdauer beträgt <span id="averageSessionDuration">...</span> mit
    <span id="averageSessionClicks">...</span> Aufrufen. Die Absprungrate
    beträgt <span id="bounceRate">...</span>%.
  </p>
  <div class="res-grid">
    <div id="chartEntryPages" data-title="Einstiegsseiten" data-type="bar"></div>
    <div id="chartExitPages" data-title="Ausstiegsseiten" data-type="bar"></div>
  </div>
  <div id="chartSuccessPages" data-title="Am häufigsten angefragt" data-type="bar"></div>
  <div id="chartErrorPages" data-title="Fehlerseiten" data-type="bar"></div>
  <h2>Besucher Flow</h2>
  <p>
    Anschließend erfolgt eine detailliertere Aufschlüsselung der einzelnen
    Sitzungen. Hier finden Sie allgemeine Informationen zur Sitzung sowie die
    Reihenfolge und Uhrzeit der besuchten Seiten.
  </p>
  <div id="sessions"></div>
</main>
<script src="./js/day.js" type="module"></script>
