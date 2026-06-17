<?php

namespace Eventilize\Controllers;

use Eventilize\Models\Dashboard;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends BaseController
{
    public function admin(Request $request, Response $response): Response
    {
        return $this->success(
            $response,
            'Admin dashboard retrieved successfully',
            (new Dashboard($this->db))->adminStats()
        );
    }
}
