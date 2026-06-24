<?php

namespace Eventilize\Controllers;

use Eventilize\Models\Event;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookmarkController extends BaseController
{
    public function toggle(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $this->currentUser($request);
            $event = (new Event($this->db))->toggleBookmark((int)$args['id'], isset($user['id']) ? (string)$user['id'] : null);

            if (!$event) {
                return $this->error($response, 'Event not found', null, 404);
            }

            return $this->success($response, 'Event bookmark updated successfully', $event);
        } catch (\InvalidArgumentException $exception) {
            return $this->error($response, $exception->getMessage(), null, 422);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to update bookmark', ['server' => $exception->getMessage()], 500);
        }
    }
}
