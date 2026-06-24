<?php

use Eventilize\Controllers\DashboardController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $controller = new DashboardController();

    $app->get('/api/admin/dashboard', [$controller, 'admin'])->add(new JwtMiddleware(['admin']));
};
