<?php

namespace Eventilize\Middleware;

use Eventilize\Helpers\ResponseHelper;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class JwtMiddleware
{
    private array $allowedRoles;

    public function __construct(array $allowedRoles = [])
    {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return ResponseHelper::error(
                new Response(),
                'Unauthorized',
                ['auth' => 'A valid Bearer token is required.'],
                401
            );
        }

        try {
            $decoded = JWT::decode($matches[1], new Key($this->secret(), 'HS256'));
            $user = isset($decoded->user) ? (array)$decoded->user : [];

            if (!$user || empty($user['id']) || empty($user['role'])) {
                return ResponseHelper::error(new Response(), 'Unauthorized', ['auth' => 'Invalid token payload.'], 401);
            }

            if ($this->allowedRoles && !in_array($user['role'], $this->allowedRoles, true)) {
                return ResponseHelper::error(new Response(), 'Forbidden', ['auth' => 'Access denied for this role.'], 403);
            }

            return $handler->handle($request->withAttribute('user', $user));
        } catch (ExpiredException $exception) {
            return ResponseHelper::error(new Response(), 'Unauthorized', ['auth' => 'Token has expired.'], 401);
        } catch (\Throwable $exception) {
            return ResponseHelper::error(new Response(), 'Unauthorized', ['auth' => 'Invalid token.'], 401);
        }
    }

    private function secret(): string
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'change_this_secret_key';

        return strlen($secret) >= 32 ? $secret : hash('sha256', $secret);
    }
}
