<?php

namespace Eventilize\Models;

use PDO;

class Notification
{
    private PDO $db;
    private User $users;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->users = new User($db);
    }

    public function listForUser(string $userIdentifier): array
    {
        $user = $this->requireUser($userIdentifier);
        $statement = $this->db->prepare(
            'SELECT n.*, e.title AS event_title
             FROM notifications n
             LEFT JOIN events e ON n.event_id = e.event_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC'
        );
        $statement->execute([$user['id']]);

        return array_map(fn ($row) => $this->transform($row), $statement->fetchAll());
    }

    public function markAsRead(int $notificationId): ?array
    {
        $this->db->prepare('UPDATE notifications SET is_read = TRUE WHERE notification_id = ?')->execute([$notificationId]);

        $statement = $this->db->prepare(
            'SELECT n.*, e.title AS event_title
             FROM notifications n
             LEFT JOIN events e ON n.event_id = e.event_id
             WHERE n.notification_id = ?'
        );
        $statement->execute([$notificationId]);
        $row = $statement->fetch();

        return $row ? $this->transform($row) : null;
    }

    public function markAllAsRead(string $userIdentifier): int
    {
        $user = $this->requireUser($userIdentifier);
        $statement = $this->db->prepare('UPDATE notifications SET is_read = TRUE WHERE user_id = ?');
        $statement->execute([$user['id']]);

        return $statement->rowCount();
    }

    public function settings(string $userIdentifier): array
    {
        $user = $this->requireUser($userIdentifier);
        $this->ensureSettings($user['id']);

        $statement = $this->db->prepare('SELECT * FROM notification_settings WHERE user_id = ?');
        $statement->execute([$user['id']]);

        return $this->transformSettings($statement->fetch());
    }

    public function updateSettings(string $userIdentifier, array $payload): array
    {
        $user = $this->requireUser($userIdentifier);
        $this->ensureSettings($user['id']);

        $statement = $this->db->prepare(
            'UPDATE notification_settings
             SET new_event = ?, upcoming_event = ?, registration_deadline = ?
             WHERE user_id = ?'
        );
        $statement->execute([
            !empty($payload['newEvent']) ? 1 : 0,
            !empty($payload['upcomingEvent']) ? 1 : 0,
            !empty($payload['registrationDeadline']) ? 1 : 0,
            $user['id'],
        ]);

        return $this->settings((string)$user['id']);
    }

    private function ensureSettings(int $userId): void
    {
        $this->db->prepare('INSERT IGNORE INTO notification_settings (user_id) VALUES (?)')->execute([$userId]);
    }

    private function requireUser(string $identifier): array
    {
        $user = $this->users->findByIdentifier($identifier);

        if (!$user) {
            throw new \InvalidArgumentException(json_encode(['user' => 'User not found.']));
        }

        return $user;
    }

    private function transform(array $row): array
    {
        return [
            'id' => (int)$row['notification_id'],
            'eventId' => isset($row['event_id']) ? (int)$row['event_id'] : null,
            'eventTitle' => $row['event_title'] ?? null,
            'title' => $row['event_title'] ?? 'Eventilize Notification',
            'message' => $row['message'],
            'isRead' => (bool)$row['is_read'],
            'createdAt' => $row['created_at'],
        ];
    }

    private function transformSettings(array $row): array
    {
        return [
            'newEvent' => (bool)$row['new_event'],
            'upcomingEvent' => (bool)$row['upcoming_event'],
            'registrationDeadline' => (bool)$row['registration_deadline'],
        ];
    }
}
