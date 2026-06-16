<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$rootPath = dirname(__DIR__);

if (file_exists($rootPath . '/.env')) {
    Dotenv::createImmutable($rootPath)->safeLoad();
}

function notifyStatus(string $label, bool $success, string $message = ''): void
{
    $status = $success ? 'SUCCESS' : 'FAILED';
    $line = "[Eventilize Setup] {$status} - {$label}";

    if ($message !== '') {
        $line .= ": {$message}";
    }

    echo $line . PHP_EOL;
}

function runSqlFile(PDO $connection, string $path): void
{
    $sql = file_get_contents($path);

    if ($sql === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $connection->exec($sql);
}

function countExistingTables(PDO $connection, string $database): int
{
    $expectedTables = [
        'users',
        'categories',
        'events',
        'bookmarks',
        'notifications',
        'event_analytics',
    ];

    $placeholders = implode(',', array_fill(0, count($expectedTables), '?'));
    $statement = $connection->prepare(
        "SELECT COUNT(*) AS total
         FROM information_schema.tables
         WHERE table_schema = ?
         AND table_name IN ({$placeholders})"
    );
    $statement->execute(array_merge([$database], $expectedTables));

    return (int)$statement->fetchColumn();
}

function hasSeedData(PDO $connection): bool
{
    $checks = [
        'SELECT COUNT(*) FROM users',
        'SELECT COUNT(*) FROM categories',
        'SELECT COUNT(*) FROM events',
    ];

    foreach ($checks as $sql) {
        if ((int)$connection->query($sql)->fetchColumn() === 0) {
            return false;
        }
    }

    return true;
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_NAME'] ?? 'eventilize';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

try {
    $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $connection = new PDO($serverDsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    notifyStatus('Database connection', true, "{$host}:{$port}");
} catch (Throwable $exception) {
    notifyStatus('Database connection', false, $exception->getMessage());
    exit(1);
}

try {
    $connection->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    if (countExistingTables($connection, $database) === 6) {
        notifyStatus('Schema creation', true, "{$database} tables already exist");
    } else {
        runSqlFile($connection, $rootPath . '/database/schema.sql');
        notifyStatus('Schema creation', true, $database);
    }
} catch (Throwable $exception) {
    notifyStatus('Schema creation', false, $exception->getMessage());
    exit(1);
}

try {
    $connection->exec("USE `{$database}`");

    if (hasSeedData($connection)) {
        notifyStatus('Seed data creation', true, 'seed data already exists');
    } else {
        runSqlFile($connection, $rootPath . '/database/seed.sql');
        notifyStatus('Seed data creation', true, $database);
    }
} catch (Throwable $exception) {
    notifyStatus('Seed data creation', false, $exception->getMessage());
    exit(1);
}
