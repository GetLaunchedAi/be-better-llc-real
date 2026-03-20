<?php

/**
 * Laravel proxy — bootstraps the Laravel app from the sibling directory.
 * Apache rewrites in .htaccess send dynamic requests here.
 */

define('LARAVEL_START', microtime(true));

$laravelBase = __DIR__ . '/../laravel';

if (file_exists($maintenance = $laravelBase . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelBase . '/vendor/autoload.php';

(require_once $laravelBase . '/bootstrap/app.php')
    ->handleRequest(\Illuminate\Http\Request::capture());
