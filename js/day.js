import { initChart } from './common.js';

const parameters = new URLSearchParams(window.location.search);
const data = await (
  await fetch(
    './api/day?file=' + parameters.get('file') + '&date=' +
      parameters.get('date')
  )
  // eslint-disable-next-line unicorn/no-await-expression-member
).json();

function timestampToString(timestamp) {
  return new Date((timestamp - 3600) * 1000).toLocaleTimeString('de');
}

function addSessions(element) {
  let sessionIndex = 1;
  for (const device of data.devices) {
    const heading = document.createElement('h3');
    const paragraph = document.createElement('p');
    const timeline = document.createElement('div');
    heading.append(document.createTextNode('Sitzung ' + sessionIndex));
    paragraph.append(
      document.createTextNode(
        'Sitzungsdauer: ' + timestampToString(device.duration)
      ),
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
document.getElementById(
  'averageSessionDuration'
).textContent = timestampToString(data.averageSessionDuration);
document.getElementById(
  'averageSessionClicks'
).textContent = data.averageSessionClicks;
document.getElementById('bounceRate').textContent = data.bounceRate;
addSessions(document.getElementById('sessions'));

initChart(
  '#chartClicksPerHour', {
    datasets: [{ values: Object.values(data.clicksPerHour) }],
    labels: Object.keys(data.clicksPerHour),
    yMarkers: [{ label: 'Durchschnitt', value: data.averageClicksPerHour }]
  }, {
    formatTooltipX: value => value + ' Uhr',
    formatTooltipY: value => value + ' Klicks'
  }
);
initChart(
  '#chartOperatingSystems', {
    datasets: [{ values: Object.values(data.operatingSystems) }],
    labels: Object.keys(data.operatingSystems)
  }, { formatTooltipY: value => value + ' Geräte' }, { xAxisMode: 'tick' }
);
initChart(
  '#chartBrowsers', {
    datasets: [{ values: Object.values(data.browsers) }],
    labels: Object.keys(data.browsers)
  }, { formatTooltipY: value => value + ' Geräte' }, { xAxisMode: 'tick' }
);
initChart(
  '#chartEntryPages',
  {
    datasets: [{ values: Object.values(data.entryPages).slice(0, 5) }],
    labels: Object.keys(data.entryPages).slice(0, 5)
  },
  { formatTooltipX: value => value.replace(' ― ', '<br>') },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartExitPages',
  {
    datasets: [{ values: Object.values(data.exitPages).slice(0, 5) }],
    labels: Object.keys(data.exitPages).slice(0, 5)
  },
  { formatTooltipX: value => value.replace(' ― ', '<br>') },
  { xAxisMode: 'tick' }
);
initChart(
  '#chartSuccessPages', {
    datasets: [{ values: Object.values(data.successPages).slice(0, 5) }],
    labels: Object.keys(data.successPages).slice(0, 5)
  }, {
    formatTooltipX: value => value.replace(' ― ', '<br>'),
    formatTooltipY: value => value + ' Klicks'
  }, { xAxisMode: 'tick' }
);
initChart(
  '#chartErrorPages', {
    datasets: [{ values: Object.values(data.errorPages).slice(0, 5) }],
    labels: Object.keys(data.errorPages).slice(0, 5)
  }, {
    formatTooltipX: value => value.replace(' ― ', '<br>'),
    formatTooltipY: value => value + ' Klicks'
  }, { xAxisMode: 'tick' }
);
