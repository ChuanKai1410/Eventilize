<?php

use Eventilize\Controllers\BookmarkController;
use Eventilize\Controllers\EventController;

return function ($app): void {
    $events = new EventController();
    $bookmarks = new BookmarkController();

    $app->get('/api/events', [$events, 'index']);
    $app->get('/api/events/{id}', [$events, 'show']);
    $app->post('/api/events', [$events, 'store']);
    $app->put('/api/events/{id}', [$events, 'update']);
    $app->delete('/api/events/{id}', [$events, 'destroy']);
    $app->patch('/api/events/{id}/status', [$events, 'updateStatus']);
    $app->post('/api/events/{id}/views', [$events, 'incrementViews']);
    $app->post('/api/events/{id}/bookmark', [$bookmarks, 'toggle']);
};
