<?php

use Eventilize\Controllers\CategoryController;

return function ($app): void {
    $controller = new CategoryController();

    $app->get('/api/categories', [$controller, 'index']);
};
