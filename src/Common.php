<?php

  // Workaround for domains not connected to ~/
  $DOCUMENT_ROOT = preg_replace(
    '=^([/a-z0-9]+/htdocs/).*$=',
    '\1',
    getenv('DOCUMENT_ROOT')
  );

  function getFile(string $path): array {
    if (str_ends_with($path, '.gz')) {
      $file = '';
      $resource = gzopen($path, 'r');
      while (!gzeof($resource)) $file .= gzread($resource, 4096);
      gzclose($resource);
      return explode(PHP_EOL, $file);
    }
    return file($path);
  }

  function countUpValue(array &$array, string $key): void {
    isset($array[$key]) ? $array[$key]++ : $array[$key] = 1;
  }

  function isRelevantEntry(string $line): bool {
    $line = strtolower($line);
    return strlen($line) > 0
      && strpos($line, $_SERVER['HTTP_HOST']) === false
      && strpos($line, 'get') !== false
      && strpos($line, '" 2') !== false
      && strpos($line, '.js') === false
      && strpos($line, '.css') === false
      && strpos($line, '.json') === false
      && strpos($line, '.ico') === false
      && strpos($line, '.svg') === false
      && strpos($line, '.png') === false
      && strpos($line, '.jpg') === false
      && strpos($line, '.xml') === false
      && strpos($line, '.txt') === false
      && strpos($line, '.ttf') === false
      && strpos($line, '.woff') === false
      && strpos($line, '.woff2') === false
      && strpos($line, '.mp3') === false
      && strpos($line, '.mp4') === false
      && strpos($line, '.pdf') === false
      && strpos($line, '.zip') === false
      && strpos($line, '.env') === false;
  }

  function isError(string $line): bool {
    $line = strtolower($line);
    return strlen($line) > 0
      && strpos($line, $_SERVER['HTTP_HOST']) === false
      && strpos($line, 'get') !== false
      && strpos($line, '" 4') !== false;
  }

  function getIpFromLine(string $line): string {
    return hash('xxh3', substr($line, 0, strpos($line, ' ')));
  }

  function getHostFromLine(string $line): string {
    $shortLine = substr($line, strposX($line, '"', 2));
    $offset = strposX($shortLine, ' ', 3) + 1;
    return substr($shortLine, $offset, strposX($shortLine, ' ', 4) - $offset);
  }

  function getRequestFromLine(string $line): string {
    $offset = strpos($line, '"GET ');
    if ($offset === false) return false;
    else $offset += 5;
    return str_replace(
      ['.html', '.php'],
      '',
      substr($line, $offset, strpos($line, ' HTTP/') - $offset)
    );
  }

  function getUserAgentFromLine(string $line): string {
    $offset = strposX($line, '"', 5) + 1;
    return substr($line, $offset, strposX($line, '"', 6) - $offset);
  }

  function getDateFromLine(string $line): string {
    return substr($line, strpos($line, '[') + 1, 11);
  }

  function getTimeFromLine(string $line): string {
    $cut = substr($line, strpos($line, '['));
    $offset = strpos($cut, ':');
    return substr($cut, $offset + 1, strpos($cut, ' ') - $offset);
  }

  function getHourFromLine(string $line): int {
    $hour = substr($line, strpos($line, '['));
    return (int) substr($hour, strpos($hour, ':') + 1, 2);
  }

  function getReadableDate(string|null $string): string {
    if ($string === null) return '...';
    return substr_replace(
      substr_replace(strip_tags($string), '. ', 6, 1),
      '. ',
      2,
      1
    );
  }

  function getReadableWeek(string|null $string): string {
    if ($string === null) return '...';
    $parts = explode('.', $string);
    foreach ($parts as $part) if (is_numeric($part)) return $part;
    return '...';
  }

  function strposX(string $haystack, string $needle, int $number = 1) {
    if (substr_count($haystack, $needle) < $number) return false;
    else return strpos(
      $haystack,
      $needle,
      $number > 1
        ? strposX($haystack, $needle, $number - 1) + strlen($needle)
        : 0
    );
  }
?>
