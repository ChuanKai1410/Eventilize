<?php

namespace Eventilize\Middleware;

use Eventilize\Helpers\ResponseHelper;
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
        
        if (empty($authHeader)) {
            $response = new Response();
            return ResponseHelper::error($response, 'Unauthorized: Token missing', ['auth' => 'Authorization header is required.'], 401);
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $response = new Response();
            return ResponseHelper::error($response, 'Unauthorized: Invalid token format', ['auth' => 'Token must be Bearer token.'], 401);
        }

        $token = $matches[1];
        $secretKey = $_ENV['JWT_SECRET'] ?? 'change_this_secret_key';

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            
            // Validate expiration explicitly
            if (isset($decoded->exp) && $decoded->exp < time()) {
                $response = new Response();
                return ResponseHelper::error($response, 'Unauthorized: Token has expired', ['auth' => 'Token has expired.'], 401);
            }
            
            $user = (array)$decoded->user;

            // Check roles if allowedRoles is specified
            if (!empty($this->allowedRoles) && !in_array($user['role'] ?? '', $this->allowedRoles, true)) {
                $response = new Response();
                return ResponseHelper::error($response, 'Forbidden: Access denied', ['auth' => 'You do not have permission to access this resource.'], 403);
            }

            // Put user into request attributes
            $request = $request->withAttribute('user', $user);

            return $handler->handle($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            $response = new Response();
            return ResponseHelper::error($response, 'Unauthorized: Token has expired', ['auth' => $e->getMessage()], 401);
        } catch (\Throwable $e) {
            $response = new Response();
            return ResponseHelper::error($response, 'Unauthorized: Invalid token', ['auth' => $e->getMessage()], 401);
        }
    }
}
