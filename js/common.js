import {
  Chart
} from 'https://unpkg.com/frappe-charts@1.6.1/dist/frappe-charts.min.esm.js';

export function initChart(id, data, tooltipOptions = {}, axisOptions = {}) {
  const { title, type } = document.querySelector(id).dataset;
  if (type === 'bar') axisOptions.xAxisMode = 'tick';
  return new Chart(
    id,
    {
      axisOptions,
      colors: ['#1976D2'],
      data,
      lineOptions: { hideDots: 1, regionFill: 1 },
      title,
      tooltipOptions,
      type,
      valuesOverPoints: 1
    }
  );
}
