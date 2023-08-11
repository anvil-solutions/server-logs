# Anvil Server Log Tool (DE)

This tool significantly enhances your ability to visualize and comprehend the
logged data from your IONOS server. Simply copy the files to a designated folder
on your IONOS webspace and establish a password during your initial login.

## Features

- Today's data
  - Clicks per hour
  - Most requested pages
- Detailed daily data
  - Clicks per hour
  - Operating systems used
  - Browsers used
  - Session durations
  - Session page request count
  - Bounce rate
  - Entry pages
  - Exit pages
  - Most requested pages
  - Error pages
  - User flow
- General history
  - Clicks per day
  - Devices per day
- General data
  - Operating systems
  - Browsers
  - Most requested pages
  - Error pages

## Development

### JavaScript Linting

Run the following command in your project's root directory to utilize eslint,
a popular linter for JavaScript:

```bash
npm run eslint
```

### PHP Linting

Run the following command in your project's root directory to scans PHP files
for syntax errors:

```bash
npm run phpl
```

## Third-Party Software

- [Frappe Charts](https://github.com/frappe/charts) (1.6.1 | MIT)
- [PHP Browser Detection](https://github.com/foroco/php-browser-detection) (2.7 | MIT)
