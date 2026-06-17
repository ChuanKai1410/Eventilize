<?php

use Eventilize\Config\Database;
use Eventilize\Helpers\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app): void {
    $app->get('/', function (Request $request, Response $response) {
        return ResponseHelper::success($response, 'Eventilize API Running');
    });

    $app->get('/api/health', function (Request $request, Response $response) {
        return ResponseHelper::success($response, 'Eventilize API is healthy');
    });

    $app->get('/api/test-db', function (Request $request, Response $response) {
        try {
            $connection = (new Database())->getConnection();
            $connection->query('SELECT 1');

            return ResponseHelper::success($response, 'Database connection successful');
        } catch (Throwable $exception) {
            return ResponseHelper::error(
                $response,
                'Database connection failed',
                ['database' => $exception->getMessage()],
                500
            );
        }
    });

    (require __DIR__ . '/auth.php')($app);
    (require __DIR__ . '/categories.php')($app);
    (require __DIR__ . '/events.php')($app);
    (require __DIR__ . '/registrations.php')($app);
    (require __DIR__ . '/notifications.php')($app);
    (require __DIR__ . '/dashboard.php')($app);
};
