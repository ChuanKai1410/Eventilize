<?php

use Eventilize\Controllers\AuthController;

return function ($app): void {
    $controller = new AuthController();

    $app->post('/api/auth/register', [$controller, 'register']);
    $app->post('/api/auth/login', [$controller, 'login']);
    $app->get('/api/users/{id}', [$controller, 'show']);
};
