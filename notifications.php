<?php

use Eventilize\Controllers\NotificationController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $controller = new NotificationController();

    $app->get('/api/users/{userId}/notifications', [$controller, 'index'])->add(new JwtMiddleware());
    $app->patch('/api/notifications/{id}/read', [$controller, 'markRead'])->add(new JwtMiddleware());
    $app->patch('/api/users/{userId}/notifications/read-all', [$controller, 'markAllRead'])->add(new JwtMiddleware());
    $app->get('/api/users/{userId}/notification-settings', [$controller, 'settings'])->add(new JwtMiddleware());
    $app->put('/api/users/{userId}/notification-settings', [$controller, 'updateSettings'])->add(new JwtMiddleware());
};

