<?php

use Eventilize\Controllers\NotificationController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $controller = new NotificationController();

    $studentOnly = new JwtMiddleware(['student']);

    $app->get('/api/users/{userId}/notifications', [$controller, 'index'])->add($studentOnly);
    $app->patch('/api/notifications/{id}/read', [$controller, 'markRead'])->add($studentOnly);
    $app->patch('/api/users/{userId}/notifications/read-all', [$controller, 'markAllRead'])->add($studentOnly);
    $app->get('/api/users/{userId}/notification-settings', [$controller, 'settings'])->add($studentOnly);
    $app->put('/api/users/{userId}/notification-settings', [$controller, 'updateSettings'])->add($studentOnly);
};
