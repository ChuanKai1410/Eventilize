<?php

use Eventilize\Helpers\ResponseHelper;
use Eventilize\Middleware\CorsMiddleware;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

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

        return ResponseHelper::error($response, $exception->getMessage(), null, $statusCode);
    }
);

$routes = require __DIR__ . '/../src/Routes/api.php';
$routes($app);

$app->run();
