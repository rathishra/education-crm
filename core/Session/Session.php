<?php
namespace Core\Session;

class Session
{
    private static ?Session $instance = null;
    private bool $started = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function start(array $config = []): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        $name = $config['name'] ?? 'educrm_session';
        $lifetime = ($config['lifetime'] ?? 120) * 60;

        ini_set('session.cookie_httponly', $config['httponly'] ?? true);
        ini_set('session.cookie_secure', $config['secure'] ?? false);
        ini_set('session.gc_maxlifetime', $lifetime);
        ini_set('session.cookie_lifetime', $lifetime);

        session_name($name);
        session_start();
        $this->started = true;

        // Process flash data from previous request
        $this->ageFlashData();
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $_SESSION;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $session = &$_SESSION;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $session[$k] = $value;
            } else {
                if (!isset($session[$k]) || !is_array($session[$k])) {
                    $session[$k] = [];
                }
                $session = &$session[$k];
            }
        }
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function remove(string $key): void
    {
        $keys = explode('.', $key);
        $session = &$_SESSION;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                unset($session[$k]);
            } else {
                if (!isset($session[$k])) return;
                $session = &$session[$k];
            }
        }
    }

    /**
     * Flash data - available only for next request
     */
    public function setFlash(string $key, $value): void
    {
        $_SESSION['_flash']['new'][$key] = $value;
    }

    public function getFlash(string $key, $default = null)
    {
        return $_SESSION['_flash']['current'][$key] ??
               $_SESSION['_flash']['new'][$key] ??
               $default;
    }

    private function ageFlashData(): void
    {
        // Move 'new' flash data to 'current', discard old 'current'
        $_SESSION['_flash']['current'] = $_SESSION['_flash']['new'] ?? [];
        $_SESSION['_flash']['new'] = [];
    }

    /**
     * Store old input for form repopulation
     */
    public function flashInput(array $input): void
    {
        foreach ($input as $key => $value) {
            if ($key !== '_token') {
                $this->setFlash('old_input.' . $key, $value);
            }
        }
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();
        $this->started = false;
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function id(): string
    {
        return session_id();
    }
}
