<?php

  $pageTitle = 'Übersicht';
  require_once('./src/layout/header.php');
?>
<main>
  <h2>Willkommen</h2>
  <p>
    Willkommen auf Ihrer Übersichtsseite.
    Mit Schließen des Browsers werden Sie automatisch abgemeldet.
    Zuletzt aktualisiert: <span id="lastRefreshed">...</span> Uhr.
  </p>
  <h2>Heutige Schnellanalyse</h2>
  <p id="quickPreview">...</p>
  <div id="chartClicksPerHour" data-title="Klicks pro Stunde" data-type="line"></div>
  <div id="chartClicksPerFile" data-title="Am häufigsten angefragt" data-type="bar"></div>
  <h2>Detailansicht</h2>
  <p>
    Die Detailansichten zeigen Ihnen eine genauere Auswertung der Daten für den gewählten Tag.
    Klicken Sie auf einen Tag um eine Detailansicht des jeweiligen Datums zu erhalten.
  </p>
  <div id="linkTable" class="week-grid"></div>
  <h2>Verlauf</h2>
  <p>
    Die folgenden Graphen zeigen Ihnen die Anzahl an Geräten und Klicks pro Tag
    für die aufgezeichnete Zeitspanne. Durchschnittlich gab es jeden Tag
    <span id="averageClicksPerDay">...</span> Klicks von
    <span id="averageDevicesPerDay">...</span> Geräten.
  </p>
  <div id="chartClicksPerDay" data-title="Klicks pro Tag" data-type="line"></div>
  <div id="chartDevicesPerDay" data-title="Geräte pro Tag" data-type="line"></div>
  <h2>Gesamtdaten</h2>
  <p>
    Unten sehen Sie die Auswertung der aufgezeichneten Daten über die gesamte Zeitspanne.
    Genauer aufgeschlüsselt sind die genutzen Browser und Betriebssysteme, sowie die meistbesuchten Seiten und die häufigsten Fehlerseiten.
  </p>
  <div class="res-grid">
    <div id="chartOperatingSystems" data-title="Genutzte Betriebssysteme" data-type="percentage"></div>
    <div id="chartBrowsers" data-title="Genutzte Browser" data-type="percentage"></div>
  </div>
  <div id="chartSuccessPages" data-title="Am häufigsten angefragt" data-type="bar"></div>
  <div id="chartErrorPages" data-title="Fehlerseiten" data-type="bar"></div>
  <h2>Einstellungen</h2>
  <ul>
    <li><a href="./password">Passwort ändern</a></li>
    <li><a href="./logout">Abmelden</a></li>
  </ul>
</main>
<script src="https://unpkg.com/frappe-charts@1.6.1/dist/frappe-charts.min.umd.js"></script>
<script src="./js/index.js" type="module"></script>
