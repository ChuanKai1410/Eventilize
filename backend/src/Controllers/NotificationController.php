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
            $user = $this->currentUser($request);

            return $this->success(
                $response,
                'Notifications retrieved successfully',
                $this->notifications->listForUser((string)($user['id'] ?? ''))
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function markRead(Request $request, Response $response, array $args): Response
    {
        $user = $this->currentUser($request);
        $notification = $this->notifications->markAsRead((int)$args['id'], isset($user['id']) ? (int)$user['id'] : null);

        if (!$notification) {
            return $this->error($response, 'Notification not found', null, 404);
        }

        return $this->success($response, 'Notification marked as read', $notification);
    }

    public function markAllRead(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->currentUser($request);

            return $this->success($response, 'All notifications marked as read', [
                'updated' => $this->notifications->markAllAsRead((string)($user['id'] ?? '')),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function settings(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->currentUser($request);

            return $this->success(
                $response,
                'Notification settings retrieved successfully',
                $this->notifications->settings((string)($user['id'] ?? ''))
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function updateSettings(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->currentUser($request);

            return $this->success(
                $response,
                'Notification settings updated successfully',
                $this->notifications->updateSettings((string)($user['id'] ?? ''), $request->getParsedBody() ?? [])
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }
}
