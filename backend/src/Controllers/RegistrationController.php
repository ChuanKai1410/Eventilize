<?php

namespace Eventilize\Controllers;

use Eventilize\Models\Registration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RegistrationController extends BaseController
{
    private Registration $registrations;

    public function __construct()
    {
        parent::__construct();
        $this->registrations = new Registration($this->db);
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->success(
                $response,
                'Registered events retrieved successfully',
                $this->registrations->listForUser((string)$args['userId'])
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function status(Request $request, Response $response, array $args): Response
    {
        try {
            return $this->success($response, 'Registration status retrieved successfully', [
                'isRegistered' => $this->registrations->isRegistered((string)$args['userId'], (int)$args['eventId']),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        }
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody() ?? [];

            return $this->success(
                $response,
                'Event registered successfully',
                $this->registrations->register((string)($body['userId'] ?? ''), (int)$args['eventId']),
                201
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\RuntimeException $exception) {
            return $this->error($response, $exception->getMessage(), null, 404);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $body = $request->getParsedBody() ?? [];

            return $this->success(
                $response,
                'Event registration cancelled successfully',
                $this->registrations->unregister((string)($body['userId'] ?? ''), (int)$args['eventId'])
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\RuntimeException $exception) {
            return $this->error($response, $exception->getMessage(), null, 404);
        }
    }
}
