<?php

namespace Eventilize\Config;

use Dotenv\Dotenv;
use PDO;

class Database
{
    private ?PDO $connection = null;

    public function __construct()
    {
        $rootPath = dirname(__DIR__, 2);

        if (file_exists($rootPath . '/.env')) {
            Dotenv::createImmutable($rootPath)->safeLoad();
        }
    }

    public function getConnection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? 'eventilize';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        error_log('[Eventilize API] SUCCESS - Database connection successful');

        return $this->connection;
    }
}
