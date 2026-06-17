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
        'event_registrations',
        'notifications',
        'notification_settings',
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
    return (int)$connection->query('SELECT COUNT(*) FROM categories')->fetchColumn() > 0;
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

function ensureReferenceData(PDO $connection): void
{
    ensureSuperAdmin($connection);

    $categories = ['Tech Talk', 'Workshop', 'Cultural', 'Sports', 'Career', 'Seminar', 'Residential'];
    $statement = $connection->prepare('INSERT IGNORE INTO categories (category_name) VALUES (?)');

    foreach ($categories as $category) {
        $statement->execute([$category]);
    }
}

function ensureSuperAdmin(PDO $connection): void
{
    $name = 'Super Admin';
    $email = 'admin@utm.my';
    $password = 'admin1234';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $connection->prepare("UPDATE users SET role = 'student' WHERE role = 'admin' AND email <> ?")
        ->execute([$email]);

    $statement = $connection->prepare('SELECT user_id FROM users WHERE email = ?');
    $statement->execute([$email]);
    $userId = $statement->fetchColumn();

    if ($userId) {
        $connection->prepare("UPDATE users SET name = ?, password_hash = ?, role = 'admin' WHERE user_id = ?")
            ->execute([$name, $hash, $userId]);
        notifyStatus('Super admin account', true, "email={$email} password={$password}");
        return;
    }

    $connection->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')")
        ->execute([$name, $email, $hash]);
    notifyStatus('Super admin account', true, "email={$email} password={$password}");
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

    if (countExistingTables($connection, $database) >= 6) {
        notifyStatus('Schema creation', true, "{$database} tables already exist");
    } else {
        runSqlFile($connection, $rootPath . '/database/schema.sql');
        notifyStatus('Schema creation', true, $database);
    }

    applySchemaCompatibility($connection, $database);
    notifyStatus('Schema compatibility', true, 'events table supports CRUD fields');
    applyRegistrationSchemaCompatibility($connection, $database);
    notifyStatus('Registration schema', true, 'student registrations table available');
    ensureReferenceData($connection);
    notifyStatus('Reference data', true, 'super admin and categories available');
} catch (Throwable $exception) {
    notifyStatus('Schema creation', false, $exception->getMessage());
    exit(1);
}

function tableExists(PDO $connection, string $database, string $table): bool
{
    $statement = $connection->prepare(
        'SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = ?
         AND table_name = ?'
    );
    $statement->execute([$database, $table]);

    return (int)$statement->fetchColumn() > 0;
}

function applyRegistrationSchemaCompatibility(PDO $connection, string $database): void
{
    $connection->exec("USE `{$database}`");

    if (!tableExists($connection, $database, 'event_registrations')) {
        $connection->exec(
            "CREATE TABLE event_registrations (
                registration_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                event_id INT NOT NULL,
                status ENUM('registered','cancelled') DEFAULT 'registered',
                registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                cancelled_at DATETIME NULL,
                FOREIGN KEY (user_id) REFERENCES users(user_id),
                FOREIGN KEY (event_id) REFERENCES events(event_id),
                UNIQUE KEY unique_registration (user_id, event_id)
            )"
        );
    }

    if (!tableExists($connection, $database, 'notification_settings')) {
        $connection->exec(
            "CREATE TABLE notification_settings (
                setting_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL UNIQUE,
                new_event BOOLEAN DEFAULT TRUE,
                upcoming_event BOOLEAN DEFAULT TRUE,
                registration_deadline BOOLEAN DEFAULT FALSE,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id)
            )"
        );
    }
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
