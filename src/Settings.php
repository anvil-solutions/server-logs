<?php

  class Settings {
    private const SETTINGS_FILE_PATH = __DIR__.'/settings.json';

    private static ?self $instance = null;

    private ?object $settings = null;

    public static function getInstance(): self {
      if (self::$instance === null) self::$instance = new Settings();
      return self::$instance;
    }

    private function __construct() {
      $this->load();
    }

    public function isSetUp(): bool {
      foreach (
        [
          'passwordHash'
        ] as $property
      ) {
        if (!property_exists($this->settings, $property)) return false;
      }
      return true;
    }

    public function getPasswordHash(): string|null {
      return $this->settings->passwordHash ?? null;
    }

    public function setPasswordHash(string|null $value): void {
      $this->changeValue('passwordHash', $value);
    }

    private function load(): void {
      $this->settings = json_decode(
        file_exists(self::SETTINGS_FILE_PATH)
        ? file_get_contents(self::SETTINGS_FILE_PATH)
        : '{}'
      );
      if ($this->settings === null) $this->settings = new stdClass();
    }

    private function changeValue(string $key, mixed $value): void {
      $this->load();
      $this->settings->$key = $value;
      $this->save();
    }

    private function save() {
      file_put_contents(self::SETTINGS_FILE_PATH, json_encode($this->settings));
    }
  }
?>
