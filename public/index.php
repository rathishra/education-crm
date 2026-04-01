<?php
/**
 * Edu Matrix - Entry Point
 * All requests are routed through this file
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoloader
spl_autoload_register(function (string $class) {
    $map = [
        'Core\\'  => BASE_PATH . '/core/',
        'App\\'   => BASE_PATH . '/app/',
    ];

    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Load helpers
require_once BASE_PATH . '/core/helpers.php';

// Boot application
$app = Core\App::getInstance();

// Load routes
require_once BASE_PATH . '/routes/web.php';
require_once BASE_PATH . '/routes/api.php';

// Dispatch request
$app->run();
