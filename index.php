<?php

use Eventilize\Helpers\ResponseHelper;
use Eventilize\Middleware\CorsMiddleware;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$rootPath = dirname(__DIR__);
if (file_exists($rootPath . '/.env')) {
    \Dotenv\Dotenv::createImmutable($rootPath)->safeLoad();
}


function runStartupDatabaseSetup(): void
{
    $rootPath = dirname(__DIR__);
    $storagePath = $rootPath . '/storage';
    $flagPath = $storagePath . '/startup-setup.flag';
    $dependencies = [
        $rootPath . '/scripts/setup-database.php',
        $rootPath . '/database/schema.sql',
        $rootPath . '/database/seed.sql',
    ];

    if (!is_dir($storagePath)) {
        mkdir($storagePath, 0775, true);
    }

    $latestDependencyTime = 0;
    foreach ($dependencies as $dependency) {
        if (file_exists($dependency)) {
            $latestDependencyTime = max($latestDependencyTime, filemtime($dependency));
        }
    }

    $currentProcessId = (string)getmypid();
    $flagProcessId = file_exists($flagPath) ? trim((string)file_get_contents($flagPath)) : '';

    if ($flagProcessId === $currentProcessId && filemtime($flagPath) >= $latestDependencyTime) {
        return;
    }

    ob_start();
    $result = require $rootPath . '/scripts/setup-database.php';
    $output = trim((string)ob_get_clean());

    if ($output !== '') {
        error_log(PHP_EOL . $output);
    }

    if ($result === 0) {
        file_put_contents($flagPath, $currentProcessId);
        touch($flagPath);
    }
}

runStartupDatabaseSetup();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new CorsMiddleware());

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function ($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails) use ($app) {
        $response = $app->getResponseFactory()->createResponse();

        if ($exception instanceof HttpNotFoundException) {
            return ResponseHelper::error($response, 'Route not found', null, 404);
        }

        $statusCode = $exception->getCode();
        if (!is_int($statusCode) || $statusCode < 400 || $statusCode > 599) {
            $statusCode = 500;
        }

        $message = $displayErrorDetails ? $exception->getMessage() : 'Internal server error';
        return ResponseHelper::error($response, $message, null, $statusCode);
    }
);

$routes = require __DIR__ . '/../src/Routes/api.php';
$routes($app);

$app->run();
