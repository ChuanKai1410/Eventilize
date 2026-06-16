<?php

namespace Eventilize\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

class ResponseHelper
{
    public static function success(
        Response $response,
        string $message,
        $data = null,
        int $statusCode = 200
    ): Response {
        self::logStatus('SUCCESS', $statusCode, $message);

        $payload = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $payload['data'] = $data;
        }

        return self::json($response, $payload, $statusCode);
    }

    public static function error(
        Response $response,
        string $message,
        $errors = null,
        int $statusCode = 400
    ): Response {
        self::logStatus('ERROR', $statusCode, $message);

        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return self::json($response, $payload, $statusCode);
    }

    private static function json(Response $response, array $payload, int $statusCode): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    private static function logStatus(string $type, int $statusCode, string $message): void
    {
        error_log(sprintf('[Eventilize API] %s %d - %s', $type, $statusCode, $message));
    }
}
