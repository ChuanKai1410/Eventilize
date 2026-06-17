<?php

namespace Eventilize\Controllers;

use Eventilize\Models\Notification;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotificationController extends BaseController
{
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->notifications = new Notification($this->db);
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->success(
                $response,
                'Notifications retrieved successfully',
                $this->notifications->listForUser((string)$args['userId'])
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function markRead(Request $request, Response $response, array $args): Response
    {
        $notification = $this->notifications->markAsRead((int)$args['id']);

        if (!$notification) {
            return $this->error($response, 'Notification not found', null, 404);
        }

        return $this->success($response, 'Notification marked as read', $notification);
    }

    public function markAllRead(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->success($response, 'All notifications marked as read', [
                'updated' => $this->notifications->markAllAsRead((string)$args['userId']),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function settings(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->success(
                $response,
                'Notification settings retrieved successfully',
                $this->notifications->settings((string)$args['userId'])
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function updateSettings(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->success(
                $response,
                'Notification settings updated successfully',
                $this->notifications->updateSettings((string)$args['userId'], $request->getParsedBody() ?? [])
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }
}
