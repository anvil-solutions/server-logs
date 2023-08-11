import {
  Chart
} from 'https://unpkg.com/frappe-charts@1.6.1/dist/frappe-charts.min.esm.js';

// eslint-disable-next-line unicorn/no-await-expression-member
const todaysLogs = await (await fetch('./api/today')).json();
// eslint-disable-next-line unicorn/no-await-expression-member
const combinedLogs = await (await fetch('./api/combined')).json();

function initChart(id, data, tooltipOptions = {}, axisOptions = {}) {
  const { title, type } = document.querySelector(id).dataset;
  return new Chart(
    id,
    {
      axisOptions,
      colors: ['#1976D2'],
      data,
      lineOptions: { hideDots: 1, regionFill: 1 },
      title,
      tooltipOptions,
      type
    }
  );
}

function getReadableWeek(file) {
  const parts = file.split('.');
  for (const part of parts) {
    const parsedNumber = parseInt(part, 10);
    if (!isNaN(parsedNumber)) return 'KW ' + parsedNumber;
  }
  return '?';
}

function addDayLinkTable(element) {
  element.textContent = '';
  const todaysElement = document.createElement('a');
  todaysElement.href = './day?file=access.log.current&date=' + todaysLogs.date;
  todaysElement.append(document.createTextNode(todaysLogs.date));
  element.append(todaysElement);
  for (
    const [file, dates] of Object.entries(combinedLogs.fileDateMap).reverse()
  ) for (const date of dates.reverse()) {
    const dateElement = document.createElement('a');
    dateElement.href = './day?file=' + file + '&date=' + date;
    dateElement.append(document.createTextNode(date));
    element.append(dateElement);
  }
}

function addWeekLinkTable(element) {
  element.textContent = '';
  for (
    const file of Object.entries(combinedLogs.fileDateMap)
      .filter(entry => entry[1].length === 7)
      .map(entry => entry[0])
      .reverse()
  ) {
    const dateElement = document.createElement('a');
    dateElement.href = './week?file=' + file;
    dateElement.append(document.createTextNode(getReadableWeek(file)));
    element.append(dateElement);
  }
}

document.getElementById(
  'lastRefreshed'
).textContent = new Date().toLocaleTimeString('de');
document.getElementById('clicks').textContent = todaysLogs.clicks.toString();
document.getElementById('devices').textContent = todaysLogs.devices.toString();
addDayLinkTable(document.getElementById('dayLinkTable'));
addWeekLinkTable(document.getElementById('weekLinkTable'));
document.getElementById(
  'averageClicksPerDay'
).textContent = combinedLogs.averageClicksPerDay.toString();
document.getElementById(
  'averageDevicesPerDay'
).textContent = combinedLogs.averageDevicesPerDay.toString();

initChart(
  '#chartClicksPerHour',
  {
    datasets: [{ values: Object.values(todaysLogs.clicksPerHour) }],
    labels: Object.keys(todaysLogs.clicksPerHour),
    yMarkers: [
      {
        label: 'Durchschnitt',
        value: todaysLogs.averageClicksPerHour
      }
    ]
  },
  {
    formatTooltipX: value => value + ' Uhr',
    formatTooltipY: value => value + ' Klicks'
  }
);
initChart(
  '#chartClicksPerFile',
  {
    datasets: [{ values: Object.values(todaysLogs.clicksPerFile).slice(0, 5) }],
    labels: Object.keys(todaysLogs.clicksPerFile).slice(0, 5)
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartClicksPerDay',
  {
    datasets: [{ values: Object.values(combinedLogs.clicksPerDay) }],
    labels: Object.keys(combinedLogs.clicksPerDay),
    yMarkers: [
      {
        label: 'Durchschnitt',
        value: combinedLogs.averageClicksPerDay
      }
    ]
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xIsSeries: true }
);
initChart(
  '#chartDevicesPerDay',
  {
    datasets: [{ values: Object.values(combinedLogs.devicesPerDay) }],
    labels: Object.keys(combinedLogs.devicesPerDay),
    yMarkers: [
      {
        label: 'Durchschnitt',
        value: combinedLogs.averageDevicesPerDay
      }
    ]
  },
  { formatTooltipY: value => value + ' Geräte' },
  { xIsSeries: true }
);
initChart(
  '#chartOperatingSystems',
  {
    datasets: [{ values: Object.values(combinedLogs.operatingSystems) }],
    labels: Object.keys(combinedLogs.operatingSystems)
  }
);
initChart(
  '#chartBrowsers',
  {
    datasets: [{ values: Object.values(combinedLogs.browsers) }],
    labels: Object.keys(combinedLogs.browsers)
  }
);
initChart(
  '#chartSuccessPages',
  {
    datasets: [
      {
        values: Object.values(combinedLogs.successPages).slice(0, 5)
      }
    ],
    labels: Object.keys(combinedLogs.successPages).slice(0, 5)
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartErrorPages',
  {
    datasets: [{ values: Object.values(combinedLogs.errorPages).slice(0, 5) }],
    labels: Object.keys(combinedLogs.errorPages).slice(0, 5)
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xAxisMode: 'tick' }
);
