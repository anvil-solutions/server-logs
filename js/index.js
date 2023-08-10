const todaysLogs = await (await fetch('./api/today')).json();
const combinedLogs = await (await fetch('./api/combined')).json();

function initChart(id, data, tooltipOptions = {}, axisOptions = {}) {
  const { title, type } = document.querySelector(id).dataset;
  return new frappe.Chart(id, {
    title: title,
    data: data,
    type: type,
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
