<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/vendor/autoload.php';

/** To set under maintenance status */
const UNDER_MAINTENANCE = false;
const MAINTENANCE_CHECK_IPS = true;
const MAINTENANCE_ALLOWED_IPS = [];
if (UNDER_MAINTENANCE) {
    $_ENV['UNDER_MAINTENANCE'] = UNDER_MAINTENANCE;
    $_ENV['MAINTENANCE_CHECK_IPS'] = MAINTENANCE_CHECK_IPS;
    $_ENV['MAINTENANCE_ALLOWED_IPS'] = MAINTENANCE_ALLOWED_IPS;
}

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

try {

    $kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
    $request = Request::createFromGlobals();

    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);

} catch (Exception $exception) {
    echo $exception->getMessage();
}