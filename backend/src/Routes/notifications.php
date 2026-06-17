<?php

use Eventilize\Controllers\NotificationController;

return function ($app): void {
    $controller = new NotificationController();

    $app->get('/api/users/{userId}/notifications', [$controller, 'index']);
    $app->patch('/api/notifications/{id}/read', [$controller, 'markRead']);
    $app->patch('/api/users/{userId}/notifications/read-all', [$controller, 'markAllRead']);
    $app->get('/api/users/{userId}/notification-settings', [$controller, 'settings']);
    $app->put('/api/users/{userId}/notification-settings', [$controller, 'updateSettings']);
};
