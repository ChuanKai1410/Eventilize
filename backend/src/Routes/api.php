<?php

use Eventilize\Config\Database;
use Eventilize\Helpers\ResponseHelper;
use Eventilize\Models\Event;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

function eventModel(): Event
{
    $database = new Database();

    return new Event($database->getConnection());
}

function validationErrorResponse(Response $response, \Throwable $exception): Response
{
    $decoded = json_decode($exception->getMessage(), true);
    $errors = is_array($decoded) ? $decoded : ['request' => $exception->getMessage()];

    return ResponseHelper::error($response, 'Validation failed', $errors, 400);
}

return function ($app): void {
    $app->get('/', function (Request $request, Response $response) {
        return ResponseHelper::success($response, 'Eventilize API Running');
    });

    $app->get('/api/health', function (Request $request, Response $response) {
        return ResponseHelper::success($response, 'Eventilize API is healthy');
    });

    $app->get('/api/test-db', function (Request $request, Response $response) {
        try {
            $database = new Database();
            $connection = $database->getConnection();
            $connection->query('SELECT 1');

            return ResponseHelper::success($response, 'Database connection successful');
        } catch (Throwable $exception) {
            error_log('[Eventilize API] ERROR - Database connection failed: ' . $exception->getMessage());

            return ResponseHelper::error(
                $response,
                'Database connection failed',
                ['database' => $exception->getMessage()],
                500
            );
        }
    });

    $app->get('/api/categories', function (Request $request, Response $response) {
        try {
            return ResponseHelper::success(
                $response,
                'Categories retrieved successfully',
                eventModel()->categories()
            );
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to retrieve categories', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->get('/api/events', function (Request $request, Response $response) {
        try {
            return ResponseHelper::success(
                $response,
                'Events retrieved successfully',
                eventModel()->all($request->getQueryParams())
            );
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to retrieve events', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->get('/api/events/{id}', function (Request $request, Response $response, array $args) {
        try {
            $event = eventModel()->find((int)$args['id']);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', null, 404);
            }

            return ResponseHelper::success($response, 'Event retrieved successfully', $event);
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to retrieve event', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->post('/api/events', function (Request $request, Response $response) {
        try {
            $event = eventModel()->create($request->getParsedBody() ?? []);

            return ResponseHelper::success($response, 'Event created successfully', $event, 201);
        } catch (InvalidArgumentException $exception) {
            return validationErrorResponse($response, $exception);
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to create event', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->put('/api/events/{id}', function (Request $request, Response $response, array $args) {
        try {
            $event = eventModel()->update((int)$args['id'], $request->getParsedBody() ?? []);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', null, 404);
            }

            return ResponseHelper::success($response, 'Event updated successfully', $event);
        } catch (InvalidArgumentException $exception) {
            return validationErrorResponse($response, $exception);
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to update event', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->delete('/api/events/{id}', function (Request $request, Response $response, array $args) {
        try {
            if (!eventModel()->delete((int)$args['id'])) {
                return ResponseHelper::error($response, 'Event not found', null, 404);
            }

            return ResponseHelper::success($response, 'Event deleted successfully');
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to delete event', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->patch('/api/events/{id}/status', function (Request $request, Response $response, array $args) {
        try {
            $body = $request->getParsedBody() ?? [];
            $event = eventModel()->updateStatus(
                (int)$args['id'],
                (string)($body['status'] ?? ''),
                (string)($body['rejectReason'] ?? '')
            );

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', null, 404);
            }

            return ResponseHelper::success($response, 'Event status updated successfully', $event);
        } catch (InvalidArgumentException $exception) {
            return validationErrorResponse($response, $exception);
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to update event status', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->post('/api/events/{id}/views', function (Request $request, Response $response, array $args) {
        try {
            $event = eventModel()->incrementViews((int)$args['id']);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', null, 404);
            }

            return ResponseHelper::success($response, 'Event view count updated successfully', $event);
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to update view count', ['server' => $exception->getMessage()], 500);
        }
    });

    $app->post('/api/events/{id}/bookmark', function (Request $request, Response $response, array $args) {
        try {
            $body = $request->getParsedBody() ?? [];
            $event = eventModel()->toggleBookmark((int)$args['id'], $body['userId'] ?? null);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', null, 404);
            }

            return ResponseHelper::success($response, 'Event bookmark updated successfully', $event);
        } catch (Throwable $exception) {
            return ResponseHelper::error($response, 'Failed to update bookmark', ['server' => $exception->getMessage()], 500);
        }
    });
};
