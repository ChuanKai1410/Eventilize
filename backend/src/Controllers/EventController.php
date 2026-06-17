<?php

namespace Eventilize\Controllers;

use Eventilize\Models\Event;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends BaseController
{
    private Event $events;

    public function __construct()
    {
        parent::__construct();
        $this->events = new Event($this->db);
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->success($response, 'Events retrieved successfully', $this->events->all($request->getQueryParams()));
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $query = $request->getQueryParams();
        $event = $this->events->find((int)$args['id'], isset($query['userId']) ? (string)$query['userId'] : null);

        if (!$event) {
            return $this->error($response, 'Event not found', null, 404);
        }

        return $this->success($response, 'Event retrieved successfully', $event);
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            return $this->success(
                $response,
                'Event created successfully',
                $this->events->create($request->getParsedBody() ?? []),
                201
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to create event', ['server' => $exception->getMessage()], 500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $event = $this->events->update((int)$args['id'], $request->getParsedBody() ?? []);

            if (!$event) {
                return $this->error($response, 'Event not found', null, 404);
            }

            return $this->success($response, 'Event updated successfully', $event);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to update event', ['server' => $exception->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            if (!$this->events->delete((int)$args['id'])) {
                return $this->error($response, 'Event not found', null, 404);
            }

            return $this->success($response, 'Event deleted successfully');
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to delete event', ['server' => $exception->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody() ?? [];
            $event = $this->events->updateStatus(
                (int)$args['id'],
                (string)($body['status'] ?? ''),
                (string)($body['rejectReason'] ?? '')
            );

            if (!$event) {
                return $this->error($response, 'Event not found', null, 404);
            }

            return $this->success($response, 'Event status updated successfully', $event);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to update event status', ['server' => $exception->getMessage()], 500);
        }
    }

    public function incrementViews(Request $request, Response $response, array $args): Response
    {
        try {
            $event = $this->events->incrementViews((int)$args['id']);

            if (!$event) {
                return $this->error($response, 'Event not found', null, 404);
            }

            return $this->success($response, 'Event view count updated successfully', $event);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to update view count', ['server' => $exception->getMessage()], 500);
        }
    }
}
