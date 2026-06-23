<?php

use Eventilize\Controllers\RegistrationController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $controller = new RegistrationController();

    $app->get('/api/users/{userId}/registrations', [$controller, 'index'])->add(new JwtMiddleware());
    $app->get('/api/users/{userId}/registrations/{eventId}', [$controller, 'status'])->add(new JwtMiddleware());
    $app->post('/api/events/{eventId}/registrations', [$controller, 'store'])->add(new JwtMiddleware(['student']));
    $app->delete('/api/events/{eventId}/registrations', [$controller, 'destroy'])->add(new JwtMiddleware(['student']));
};

