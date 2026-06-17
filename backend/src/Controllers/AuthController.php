<?php

namespace Eventilize\Controllers;

use Eventilize\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends BaseController
{
    private User $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = new User($this->db);
    }

    public function register(Request $request, Response $response): Response
    {
        try {
            $user = $this->users->register($request->getParsedBody() ?? []);

            return $this->success($response, 'Account registered successfully', ['user' => $user], 201);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to register account', ['server' => $exception->getMessage()], 500);
        }
    }

    public function login(Request $request, Response $response): Response
    {
        try {
            $body = $request->getParsedBody() ?? [];
            $user = $this->users->login((string)($body['email'] ?? ''), (string)($body['password'] ?? ''));

            return $this->success($response, 'Login successful', ['user' => $user]);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to login', ['server' => $exception->getMessage()], 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $user = $this->users->findByIdentifier((string)$args['id']);

        if (!$user) {
            return $this->error($response, 'User not found', null, 404);
        }

        return $this->success($response, 'User retrieved successfully', $user);
    }
}
