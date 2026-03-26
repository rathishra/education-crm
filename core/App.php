<?php
namespace Core;

use Core\Database\Database;
use Core\Router\Router;
use Core\Session\Session;

class App
{
    private static ?App $instance = null;
    private array $config = [];
    private ?Database $db = null;
    private ?Router $router = null;
    private ?Session $session = null;

    private function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->initSession();
        $this->initDatabase();
        \Core\Database\SchemaRepair::repair();
        $this->setTimezone();
        $this->setErrorHandling();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnvironment(): void
    {
        $envFile = BASE_PATH . '/.env';
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    private function loadConfig(): void
    {
        $configPath = BASE_PATH . '/config';
        foreach (glob($configPath . '/*.php') as $file) {
            $key = basename($file, '.php');
            $this->config[$key] = require $file;
        }
    }

    private function initSession(): void
    {
        $this->session = Session::getInstance();
        $this->session->start($this->config('app.session', []));
    }

    private function initDatabase(): void
    {
        $this->db = Database::getInstance($this->config('database'));
    }

    private function setTimezone(): void
    {
        date_default_timezone_set($this->config('app.timezone', 'Asia/Kolkata'));
    }

    private function setErrorHandling(): void
    {
        if ($this->config('app.debug', false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    public function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function db(): Database
    {
        return $this->db;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function router(): Router
    {
        if ($this->router === null) {
            $this->router = new Router();
        }
        return $this->router;
    }

    public function run(): void
    {
        $this->router()->dispatch();
    }
}
