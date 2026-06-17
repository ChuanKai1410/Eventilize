<?php

use Eventilize\Controllers\DashboardController;

return function ($app): void {
    $controller = new DashboardController();

    $app->get('/api/admin/dashboard', [$controller, 'admin']);
};
