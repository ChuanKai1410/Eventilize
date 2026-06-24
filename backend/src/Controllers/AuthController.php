<?php

namespace Eventilize\Controllers;

use Firebase\JWT\JWT;
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

            return $this->success(
                $response,
                'Account registered successfully',
                ['user' => $user, 'token' => $this->tokenFor($user)],
                201
            );
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

            return $this->success($response, 'Login successful', ['user' => $user, 'token' => $this->tokenFor($user)]);
        } catch (\InvalidArgumentException $exception) {
            return $this->validationError($response, $exception);
        } catch (\Throwable $exception) {
            return $this->error($response, 'Failed to login', ['server' => $exception->getMessage()], 500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $currentUser = $this->currentUser($request);
        $requestedId = (string)$args['id'];

        if (($currentUser['role'] ?? '') !== 'admin' && $requestedId !== (string)($currentUser['id'] ?? '') && $requestedId !== ($currentUser['email'] ?? '')) {
            return $this->error($response, 'Forbidden', ['auth' => 'You can only view your own profile.'], 403);
        }

        $user = $this->users->findByIdentifier((string)$args['id']);

        if (!$user) {
            return $this->error($response, 'User not found', null, 404);
        }

        return $this->success($response, 'User retrieved successfully', $user);
    }

    private function tokenFor(array $user): string
    {
        $now = time();

        return JWT::encode([
            'iat' => $now,
            'exp' => $now + (60 * 60 * 8),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'organizerName' => $user['organizerName'] ?? null,
            ],
        ], $this->jwtSecret(), 'HS256');
    }

    private function jwtSecret(): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'change_this_secret_key';

        return strlen($secret) >= 32 ? $secret : hash('sha256', $secret);
    }
}
