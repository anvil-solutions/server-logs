import { Chart } from 'https://unpkg.com/frappe-charts@1.6.1/dist/frappe-charts.min.esm.js';

const parameters = new URLSearchParams(window.location.search);
const data = await (await fetch('./api/details?i=' + parameters.get('i') + '&j=' + parameters.get('j'))).json();

function initChart(id, data, tooltipOptions = {}, axisOptions = {}) {
  const { title, type } = document.querySelector(id).dataset;
  return new Chart(
    id,
    {
      title,
      data,
      type,
      colors: ['#1976D2'],
      lineOptions: { regionFill: 1, hideDots: 1 },
      axisOptions,
      tooltipOptions
    }
  );
}

function timestampToString(timestamp) {
  return new Date((timestamp - 3600) * 1000).toLocaleTimeString('de')
}

function addSessions(element) {
  let sessionIndex = 1;
  for (const device of data.devices) {
    const heading = document.createElement('h3');
    const paragraph = document.createElement('p');
    const timeline = document.createElement('div');
    heading.append(document.createTextNode('Sitzung ' + sessionIndex));
    paragraph.append(
      document.createTextNode('Sitzungsdauer: ' + timestampToString(device.duration)),
      document.createElement('br'),
      document.createTextNode('Seitenaufrufe: ' + device.requests.length),
      document.createElement('br'),
      document.createTextNode('Betriebssystem: ' + device.operatingSystem),
      document.createElement('br'),
      document.createTextNode('Browser: ' + device.browser)
    );
    timeline.classList.add('timeline');
    for (const flow of device.requests) {
      const timelineEntry = document.createElement('div');
      const path = document.createElement('div');
      const domain = document.createElement('div');
      const domainSmall = document.createElement('small');
      const time = document.createElement('div');
      const timeSmall = document.createElement('small');
      const separator = document.createElement('span');
      path.append(document.createTextNode(flow[1]));
      domainSmall.append(document.createTextNode(flow[2]));
      domain.append(domainSmall);
      timeSmall.append(document.createTextNode(flow[0]));
      time.append(timeSmall);
      timelineEntry.append(path, domain, time);
      separator.classList.add('separator');
      timeline.append(timelineEntry, separator);
    }
    element.append(heading, paragraph, timeline);
    sessionIndex++;
  }
}

document.getElementById('clicks').textContent = data.clicks;
document.getElementById('devices').textContent = data.devices.length;
document.getElementById('averageSessionDuration').textContent = timestampToString(data.averageSessionDuration);
document.getElementById('averageSessionClicks').textContent = data.averageSessionClicks;
document.getElementById('bounceRate').textContent = data.bounceRate;
addSessions(document.getElementById('sessions'))

initChart(
  '#chartClicksPerHour',
  {
    labels: Object.keys(data.clicksPerHour),
    datasets: [{ values: Object.values(data.clicksPerHour) }],
    yMarkers: [{ label: "Durchschnitt", value: data.averageClicksPerHour }]
  },
  {
    formatTooltipX: d => d + ' Uhr',
    formatTooltipY: d => d + ' Klicks'
  }
);
initChart(
  '#chartOperatingSystems',
  {
    labels: Object.keys(data.operatingSystems),
    datasets: [{ values: Object.values(data.operatingSystems) }]
  },
  { formatTooltipY: d => d + ' Geräte' },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartBrowsers',
  {
    labels: Object.keys(data.browsers),
    datasets: [{ values: Object.values(data.browsers) }]
  },
  { formatTooltipY: d => d + ' Geräte' },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartEntryPages',{
    labels: Object.keys(data.entryPages).slice(0, 5),
    datasets: [{ values: Object.values(data.entryPages).slice(0, 5) }]
  },
  {},
  { xAxisMode: 'tick' }
);
initChart(
  '#chartExitPages',{
    labels: Object.keys(data.exitPages).slice(0, 5),
    datasets: [{ values: Object.values(data.exitPages).slice(0, 5) }]
  },
  {},
  { xAxisMode: 'tick' }
);
initChart(
  '#chartSuccessPages',
  {
    labels: Object.keys(data.successPages).slice(0, 5),
    datasets: [{ values: Object.values(data.successPages).slice(0, 5) }]
  },
  { formatTooltipY: d => d + ' Klicks' },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartErrorPages',
  {
    labels: Object.keys(data.errorPages).slice(0, 5),
    datasets: [{ values: Object.values(data.errorPages).slice(0, 5) }]
  },
  { formatTooltipY: d => d + ' Klicks' },
  { xAxisMode: 'tick' }
);
