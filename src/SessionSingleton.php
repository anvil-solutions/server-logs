<?php

  require_once __DIR__.'/Settings.php';

  class Session {
    private const SESSION_NAME = 'SESSION_LOGS';
    private const SESSION_PATH = '/';

    private static ?self $instance = null;

    private string $warningMessage = '';

    public static function getInstance(): self {
      if (self::$instance === null) self::$instance = new Session();
      return self::$instance;
    }

    private function __construct() {
      session_set_cookie_params(0, self::SESSION_PATH, null, true, true);
      session_name(self::SESSION_NAME);
      session_start();
    }

    public function isLoggedIn(): bool {
      $loggedIn = isset($_SESSION['loggedIn']) && !empty($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === $_SERVER['HTTP_USER_AGENT'];
      if ($loggedIn !== true) $_SESSION['loggedIn'] = '';
      return $loggedIn;
    }

    public function login(string $password, string $csrf): void {
      if ($this->isLoggedIn()) return;
      if (
        password_verify(
          $password,
          Settings::getInstance()->getPasswordHash()
        )
        && $this->matchesCSRF($csrf)
      ) {
        $_SESSION['loggedIn'] = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['trys'] = 0;
      } else {
        $this->addFailedAttempt();
        $this->warningMessage = 'Das eingegebene Kennwort ist ungültig. Bitte
          überprüfen Sie es und versuchen Sie es erneut oder kontaktieren Sie
          den Support für weitere Unterstützung.';
      }
    }

    public function logout(): void {
      $_SESSION['loggedIn'] = '';
    }

    public function canLogin(): bool {
      return !(isset($_SESSION['trys']) && $_SESSION['trys'] > 5);
    }

    public function addFailedAttempt(): void {
      $_SESSION['loggedIn'] = '';
      isset($_SESSION['trys']) ? $_SESSION['trys']++ : $_SESSION['trys'] = 1;
    }

    public function getWarningMessage(): string {
      return $this->warningMessage;
    }

    // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
    public function getCSRFToken(): string {
      if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(64));
      return $_SESSION['csrf'];
    }

    // phpcs:ignore Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
    public function matchesCSRF(string|null $token): bool {
      $matches = $token === $this->getCSRFToken();
      $_SESSION['csrf'] = bin2hex(random_bytes(64));
      return $matches;
    }
  }
?>
