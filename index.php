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
<script type="module">
  const todaysLogs = await (await fetch('./api/today')).json();
  const combinedLogs = await (await fetch('./api/combined')).json();

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

  function addLinkTable(element) {
    element.textContent = '';
    const todaysElement = document.createElement('a');
    todaysElement.href = './details?i=access.log.current&j=' + todaysLogs.date;
    todaysElement.append(document.createTextNode(todaysLogs.date));
    element.append(todaysElement)
    for (const [file, dates] of Object.entries(combinedLogs.fileDateMap).reverse()) {
      for (const date of dates.reverse()) {
        const dateElement = document.createElement('a');
        dateElement.href = './details?i=' + file + '&j=' + date;
        dateElement.append(document.createTextNode(date));
        element.append(dateElement)
      }
    }
  }

  document.getElementById('lastRefreshed').textContent = new Date().toLocaleTimeString('de');
  document.getElementById('quickPreview').textContent = todaysLogs === null
    ? 'Es wurde kein Zugriffsprotokoll gefunden.'
    : 'Heute gab es insgesamt ' + todaysLogs.clicks.toString() + ' Aufrufe von ' +
      todaysLogs.devices.length.toString() + ' unterschiedlichen Geräten.';
  addLinkTable(document.getElementById('linkTable'));
  document.getElementById('averageClicksPerDay').textContent = combinedLogs.averageClicksPerDay.toString();
  document.getElementById('averageDevicesPerDay').textContent = combinedLogs.averageDevicesPerDay.toString();

  initChart(
    '#chartClicksPerHour',
    {
      labels: Object.keys(todaysLogs.clicksPerHour),
      datasets: [{ values: Object.values(todaysLogs.clicksPerHour) }],
      yMarkers: [{ label: "Durchschnitt", value: todaysLogs.averageClicksPerHour }]
    },
    {
      formatTooltipX: d => d + ' Uhr',
      formatTooltipY: d => d + ' Klicks'
    }
  );
  initChart(
    '#chartClicksPerFile',
    {
      labels: Object.keys(todaysLogs.clicksPerFile).slice(0, 5),
      datasets: [{ values: Object.values(todaysLogs.clicksPerFile).slice(0, 5) }]
    },
    { formatTooltipY: d => d + ' Klicks' },
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartClicksPerDay',
    {
      labels: Object.keys(combinedLogs.clicksPerDay),
      datasets: [{ values: Object.values(combinedLogs.clicksPerDay) }],
      yMarkers: [{ label: "Durchschnitt", value:combinedLogs.averageClicksPerDay }]
    },
    { formatTooltipY: d => d + ' Klicks' },
    { xIsSeries: true }
  );
  initChart(
    '#chartDevicesPerDay',
    {
      labels: Object.keys(combinedLogs.devicesPerDay),
      datasets: [{ values: Object.values(combinedLogs.devicesPerDay) }],
      yMarkers: [{ label: "Durchschnitt", value: combinedLogs.averageDevicesPerDay }]
    },
    { formatTooltipY: d => d + ' Geräte' },
    { xIsSeries: true }
  );
  initChart(
    '#chartOperatingSystems',
    {
      labels: Object.keys(combinedLogs.operatingSystems),
      datasets: [{ values: Object.values(combinedLogs.operatingSystems) }]
    }
  );
  initChart(
    '#chartBrowsers',
    {
      labels: Object.keys(combinedLogs.browsers),
      datasets: [{ values: Object.values(combinedLogs.browsers) }]
    }
  );
  initChart(
    '#chartSuccessPages',
    {
      labels: Object.keys(combinedLogs.successPages).slice(0, 5),
      datasets: [{ values: Object.values(combinedLogs.successPages).slice(0, 5) }]
    },
    { formatTooltipY: d => d + ' Klicks' },
    { xAxisMode: 'tick' }
  );
  initChart(
    '#chartErrorPages',
    {
      labels: Object.keys(combinedLogs.errorPages).slice(0, 5),
      datasets: [{ values: Object.values(combinedLogs.errorPages).slice(0, 5) }]
    },
    { formatTooltipY: d => d + ' Klicks' },
    { xAxisMode: 'tick' }
  );
</script>
