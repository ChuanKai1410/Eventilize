<?php

namespace Eventilize\Controllers;

use Eventilize\Models\Event;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryController extends BaseController
{
    public function index(Request $request, Response $response): Response
    {
        return $this->success(
            $response,
            'Categories retrieved successfully',
            (new Event($this->db))->categories()
        );
    }
}
