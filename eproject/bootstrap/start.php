<?php
/**
 * Laravel 4 bootstrap with Dotenv support (PHP 7 fork)
 */

require __DIR__ . '/../vendor/autoload.php';

/* 1) Load .env (support namespaced & legacy phpdotenv) */
$envPath = realpath(__DIR__ . '/..'); // e.g. C:\laragon\www\eproject
if ($envPath && file_exists($envPath.'/.env')) {
    if (class_exists('Dotenv\\Dotenv')) {
        // Namespaced API (phpdotenv v1/v2 style)
        $dotenv = new Dotenv\Dotenv($envPath);
        $dotenv->load();
    } elseif (class_exists('Dotenv')) {
        // Legacy global class API
        Dotenv::load($envPath);
    }
}

/* 2) env() helper for L4 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        if (array_key_exists($key, $_ENV))    return $_ENV[$key];
        if (array_key_exists($key, $_SERVER)) return $_SERVER[$key];
        $v = getenv($key);
        return ($v !== false && $v !== null) ? $v : $default;
    }
}

/* 3) Create The Application */
$app = new Illuminate\Foundation\Application;

/* 4) Detect The Application Environment */
$environment = env('APP_ENV');
if (!is_string($environment) || $environment === '') {
    // fallback: simple hostname map
    $map = array(
        'local' => array('machineName'),
    );
    $host = gethostname() ?: php_uname('n');
    $environment = 'production';
    foreach ($map as $envName => $hosts) {
        if (in_array($host, (array) $hosts, true)) {
            $environment = $envName;
            break;
        }
    }
}

/* IMPORTANT: pass a CLOSURE that returns the name */
$env = $app->detectEnvironment(function () use ($environment) {
    return $environment;
});

/* 5) Bind Paths */
$app->bindInstallPaths(require __DIR__ . '/paths.php');

/* 6) Load The Application */
$framework = $app['path.base'] . '/vendor/laravel/framework/src';
require $framework . '/Illuminate/Foundation/start.php';

/* 7) Return The Application */
return $app;
