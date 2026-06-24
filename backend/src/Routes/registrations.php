<?php

use Eventilize\Controllers\RegistrationController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $controller = new RegistrationController();

    $studentOnly = new JwtMiddleware(['student']);

    $app->get('/api/users/{userId}/registrations', [$controller, 'index'])->add($studentOnly);
    $app->get('/api/users/{userId}/registrations/{eventId}', [$controller, 'status'])->add($studentOnly);
    $app->post('/api/events/{eventId}/registrations', [$controller, 'store'])->add($studentOnly);
    $app->delete('/api/events/{eventId}/registrations', [$controller, 'destroy'])->add($studentOnly);
};
