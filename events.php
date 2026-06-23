<?php

use Eventilize\Controllers\BookmarkController;
use Eventilize\Controllers\EventController;
use Eventilize\Middleware\JwtMiddleware;

return function ($app): void {
    $events = new EventController();
    $bookmarks = new BookmarkController();

    $app->get('/api/events', [$events, 'index']);
    $app->get('/api/events/{id}', [$events, 'show']);
    $app->post('/api/events', [$events, 'store'])->add(new JwtMiddleware(['organizer', 'admin']));
    $app->put('/api/events/{id}', [$events, 'update'])->add(new JwtMiddleware(['organizer', 'admin']));
    $app->delete('/api/events/{id}', [$events, 'destroy'])->add(new JwtMiddleware(['organizer', 'admin']));
    $app->patch('/api/events/{id}/status', [$events, 'updateStatus'])->add(new JwtMiddleware(['admin']));
    $app->post('/api/events/{id}/views', [$events, 'incrementViews']);
    $app->post('/api/events/{id}/bookmark', [$bookmarks, 'toggle'])->add(new JwtMiddleware(['student']));
};

