# Anvil Server Log Tool (DE)
This tool allows you to view and interpret the logged data on your IONOS server a lot easier and clearer.

## Features
- Today's data
  - Clicks per hour
  - Clicks per country
  - Devices per country
  - Operating systems used
  - Browsers used
- Daily data
  - Clicks per day
  - Devices per day
- General monthly traffic data

## Libraries Used
- [Frappe Charts](https://github.com/frappe/charts) (MIT License)
- [PHP Browser Detection](https://github.com/foroco/php-browser-detection) (MIT License)

## Getting Started
Just copy the files into a folder on your IONOS web space and change the [password hash](https://github.com/anvil-solutions/server-logs/blob/main/src/Common.php#L3).