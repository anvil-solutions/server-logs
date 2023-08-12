<?php

  $pageTitle = 'Übersicht';
  require_once(__DIR__.'/src/layout/header.php');
?>
<main>
  <h2>Willkommen</h2>
  <p>
    Willkommen auf Ihrer Übersichtsseite. Mit Schließen des Browsers werden Sie
    automatisch abgemeldet. Zuletzt aktualisiert um
    <span id="lastRefreshed">...</span> Uhr.
  </p>
  <h2>Heutige Schnellanalyse</h2>
  <p>
    Heute wurden insgesamt <span id="clicks">...</span> Aufrufe von
    <span id="devices">...</span> verschiedenen Geräten verzeichnet.
  </p>
  <div id="chartClicksPerHour" data-title="Klicks pro Stunde" data-type="line"></div>
  <div id="chartClicksPerFile" data-title="Am häufigsten angefragt" data-type="bar"></div>
  <h2>Detaillierte Ansicht</h2>
  <p>
    Die detaillierten Ansichten bieten Ihnen eine genauere Auswertung der Daten
    für den ausgewählten Tag oder die gewählte Woche. Klicken Sie auf einen
    Link, um eine detaillierte Ansicht des jeweiligen Eintrags zu erhalten.
  </p>
  <h3>Tagesdetails</h3>
  <div id="dayLinkTable" class="week-grid"></div>
  <h3>Wochendetails</h3>
  <div id="weekLinkTable" class="week-grid"></div>
  <h2>Verlauf</h2>
  <p>
    Die folgenden Diagramme zeigen Ihnen die Anzahl an Geräten und Klicks pro Tag
    für den erfassten Zeitraum. Durchschnittlich gab es jeden Tag
    <span id="averageClicksPerDay">...</span> Klicks von
    <span id="averageDevicesPerDay">...</span> Geräten.
  </p>
  <div id="chartClicksPerDay" data-title="Klicks pro Tag" data-type="line"></div>
  <div id="chartDevicesPerDay" data-title="Geräte pro Tag" data-type="line"></div>
  <h2>Gesamtdaten</h2>
  <p>
    Nachfolgend sehen Sie eine Auswertung der erfassten Daten über den gesamten
    Zeitraum. Dazu gehören detaillierte Informationen zu genutzten Browsern und
    Betriebssystemen sowie die am häufigsten besuchten Seiten und die
    prominentesten Fehlerseiten.
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
<script src="./js/index.js" type="module"></script>
