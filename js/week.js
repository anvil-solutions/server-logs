import {
  Chart
} from 'https://unpkg.com/frappe-charts@1.6.1/dist/frappe-charts.min.esm.js';

const parameters = new URLSearchParams(window.location.search);
const data = await (
  await fetch('./api/week?file=' + parameters.get('file'))
  // eslint-disable-next-line unicorn/no-await-expression-member
).json();

function initChart(id, chartData, tooltipOptions = {}, axisOptions = {}) {
  const { title, type } = document.querySelector(id).dataset;
  return new Chart(
    id,
    {
      axisOptions,
      colors: ['#1976D2'],
      data: chartData,
      lineOptions: { hideDots: 1, regionFill: 1 },
      title,
      tooltipOptions,
      type
    }
  );
}

function timestampToString(timestamp) {
  return new Date((timestamp - 3600) * 1000).toLocaleTimeString('de');
}

document.getElementById('clicks').textContent = data.clicks;
document.getElementById('devices').textContent = data.devices;
document.getElementById(
  'averageSessionDuration'
).textContent = timestampToString(data.averageSessionDuration);
document.getElementById(
  'averageSessionClicks'
).textContent = data.averageSessionClicks;
document.getElementById('bounceRate').textContent = data.bounceRate;

initChart(
  '#chartClicksPerDay',
  {
    datasets: [{ values: Object.values(data.clicksPerDay) }],
    labels: Object.keys(data.clicksPerDay),
    yMarkers: [{ label: 'Durchschnitt', value: data.averageClicksPerDay }]
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xIsSeries: true }
);
initChart(
  '#chartOperatingSystems',
  {
    datasets: [{ values: Object.values(data.operatingSystems) }],
    labels: Object.keys(data.operatingSystems)
  },
  {},
  { xAxisMode: 'tick' }
);
initChart(
  '#chartBrowsers',
  {
    datasets: [{ values: Object.values(data.browsers) }],
    labels: Object.keys(data.browsers)
  },
  {},
  { xAxisMode: 'tick' }
);
initChart(
  '#chartEntryPages', {
    datasets: [{ values: Object.values(data.entryPages).slice(0, 5) }],
    labels: Object.keys(data.entryPages).slice(0, 5)
  }, {}, { xAxisMode: 'tick' }
);
initChart(
  '#chartExitPages', {
    datasets: [{ values: Object.values(data.exitPages).slice(0, 5) }],
    labels: Object.keys(data.exitPages).slice(0, 5)
  }, {}, { xAxisMode: 'tick' }
);
initChart(
  '#chartSuccessPages',
  {
    datasets: [{ values: Object.values(data.successPages).slice(0, 5) }],
    labels: Object.keys(data.successPages).slice(0, 5)
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartErrorPages',
  {
    datasets: [{ values: Object.values(data.errorPages).slice(0, 5) }],
    labels: Object.keys(data.errorPages).slice(0, 5)
  },
  { formatTooltipY: value => value + ' Klicks' },
  { xAxisMode: 'tick' }
);
