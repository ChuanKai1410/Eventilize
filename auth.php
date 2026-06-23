<?php

use Eventilize\Controllers\AuthController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $controller = new AuthController();

    $app->post('/api/auth/register', [$controller, 'register']);
    $app->post('/api/auth/login', [$controller, 'login']);
    $app->post('/api/auth/logout', [$controller, 'logout'])->add(new JwtMiddleware());
    $app->get('/api/users/{id}', [$controller, 'show'])->add(new JwtMiddleware());
};

