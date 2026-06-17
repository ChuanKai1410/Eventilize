<?php

namespace Eventilize\Controllers;

use Eventilize\Config\Database;
use Eventilize\Helpers\ResponseHelper;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;

abstract class BaseController
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    protected function success(Response $response, string $message, $data = null, int $statusCode = 200): Response
    {
        return ResponseHelper::success($response, $message, $data, $statusCode);
    }

    protected function error(Response $response, string $message, $errors = null, int $statusCode = 400): Response
    {
        return ResponseHelper::error($response, $message, $errors, $statusCode);
    }

    protected function validationError(Response $response, \Throwable $exception): Response
    {
        $decoded = json_decode($exception->getMessage(), true);
        $errors = is_array($decoded) ? $decoded : ['request' => $exception->getMessage()];

        return $this->error($response, 'Validation failed', $errors, 400);
    }
}
