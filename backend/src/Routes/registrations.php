<?php

use Eventilize\Controllers\RegistrationController;

return function ($app): void {
    $controller = new RegistrationController();

    $app->get('/api/users/{userId}/registrations', [$controller, 'index']);
    $app->get('/api/users/{userId}/registrations/{eventId}', [$controller, 'status']);
    $app->post('/api/events/{eventId}/registrations', [$controller, 'store']);
    $app->delete('/api/events/{eventId}/registrations', [$controller, 'destroy']);
};
