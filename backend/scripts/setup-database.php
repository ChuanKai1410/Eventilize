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

function columnExists(PDO $connection, string $database, string $table, string $column): bool
{
    $statement = $connection->prepare(
        'SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = ?
         AND table_name = ?
         AND column_name = ?'
    );
    $statement->execute([$database, $table, $column]);

    return (int)$statement->fetchColumn() > 0;
}

function applySchemaCompatibility(PDO $connection, string $database): void
{
    $connection->exec("USE `{$database}`");
    $connection->exec("ALTER TABLE events MODIFY status ENUM('draft','pending','approved','rejected') DEFAULT 'pending'");
    $connection->exec('ALTER TABLE events MODIFY event_image LONGTEXT');

    if (!columnExists($connection, $database, 'events', 'poster_images')) {
        $connection->exec('ALTER TABLE events ADD poster_images LONGTEXT NULL AFTER event_image');
    }

    if (!columnExists($connection, $database, 'events', 'reject_reason')) {
        $connection->exec('ALTER TABLE events ADD reject_reason TEXT NULL AFTER status');
    }

    if (!columnExists($connection, $database, 'events', 'status_updated_at')) {
        $connection->exec('ALTER TABLE events ADD status_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER reject_reason');
    }
}

function ensureDemoReferenceData(PDO $connection): void
{
    $statement = $connection->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR name = ?");
    $statement->execute(['organizer@utm.my', 'Computing Students Society']);

    if ((int)$statement->fetchColumn() === 0) {
        $connection->prepare(
            "INSERT INTO users (name, email, password_hash, role)
             VALUES ('Computing Students Society', 'organizer@utm.my', 'hash123', 'organizer')"
        )->execute();
    }

    $categories = ['Tech Talk', 'Workshop', 'Cultural', 'Sports', 'Career', 'Seminar', 'Residential'];
    $statement = $connection->prepare('INSERT IGNORE INTO categories (category_name) VALUES (?)');

    foreach ($categories as $category) {
        $statement->execute([$category]);
    }
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

    applySchemaCompatibility($connection, $database);
    notifyStatus('Schema compatibility', true, 'events table supports CRUD fields');
    ensureDemoReferenceData($connection);
    notifyStatus('Demo reference data', true, 'organizer and categories available');
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
