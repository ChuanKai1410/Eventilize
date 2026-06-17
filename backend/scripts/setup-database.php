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
    ensureDemoUser($connection, 'Ahmad Student', 'student@utm.my', 'student123', 'student');
    ensureDemoUser($connection, 'Computing Students Society', 'organizer@utm.my', 'organizer123', 'organizer');
    ensureDemoUser($connection, 'Platform Admin', 'admin@utm.my', 'admin1234', 'admin');

    $categories = ['Tech Talk', 'Workshop', 'Cultural', 'Sports', 'Career', 'Seminar', 'Residential'];
    $statement = $connection->prepare('INSERT IGNORE INTO categories (category_name) VALUES (?)');

    foreach ($categories as $category) {
        $statement->execute([$category]);
    }
}

function ensureDemoUser(PDO $connection, string $name, string $email, string $password, string $role): void
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $statement = $connection->prepare('SELECT user_id FROM users WHERE email = ?');
    $statement->execute([$email]);
    $userId = $statement->fetchColumn();

    if ($userId) {
        $connection->prepare('UPDATE users SET name = ?, password_hash = ?, role = ? WHERE user_id = ?')
            ->execute([$name, $hash, $role, $userId]);
        return;
    }

    $connection->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)')
        ->execute([$name, $email, $hash, $role]);
}

function ensureDemoRegistrations(PDO $connection): void
{
    $studentId = $connection->query("SELECT user_id FROM users WHERE email = 'student@utm.my' LIMIT 1")->fetchColumn();

    if (!$studentId) {
        return;
    }

    $eventIds = $connection
        ->query("SELECT event_id FROM events WHERE status = 'approved' ORDER BY event_date ASC LIMIT 4")
        ->fetchAll(PDO::FETCH_COLUMN);

    $statement = $connection->prepare(
        "INSERT INTO event_registrations (user_id, event_id, status, registered_at, cancelled_at)
         VALUES (?, ?, 'registered', NOW(), NULL)
         ON DUPLICATE KEY UPDATE status = 'registered', cancelled_at = NULL"
    );

    foreach ($eventIds as $eventId) {
        $statement->execute([$studentId, $eventId]);
    }
}

function ensureDemoNotifications(PDO $connection): void
{
    $studentId = $connection->query("SELECT user_id FROM users WHERE email = 'student@utm.my' LIMIT 1")->fetchColumn();

    if (!$studentId) {
        return;
    }

    $connection->prepare('INSERT IGNORE INTO notification_settings (user_id) VALUES (?)')->execute([$studentId]);

    $countStatement = $connection->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
    $countStatement->execute([$studentId]);

    if ((int)$countStatement->fetchColumn() > 0) {
        return;
    }

    $eventId = $connection->query("SELECT event_id FROM events WHERE status = 'approved' ORDER BY event_date ASC LIMIT 1")->fetchColumn();
    $statement = $connection->prepare('INSERT INTO notifications (user_id, event_id, message, is_read) VALUES (?, ?, ?, ?)');
    $statement->execute([$studentId, $eventId ?: null, 'Welcome to Eventilize. Your registered events are now synced with the database.', 0]);
    $statement->execute([$studentId, $eventId ?: null, 'Reminder: Check your upcoming registered events.', 0]);
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
    ensureDemoReferenceData($connection);
    notifyStatus('Demo reference data', true, 'organizer and categories available');
    ensureDemoRegistrations($connection);
    notifyStatus('Demo registrations', true, 'student registered events available');
    ensureDemoNotifications($connection);
    notifyStatus('Demo notifications', true, 'student notifications available');
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
