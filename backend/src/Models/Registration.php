<?php

namespace Eventilize\Models;

use PDO;

class Registration
{
    private PDO $db;
    private Event $events;
    private User $users;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->events = new Event($db);
        $this->users = new User($db);
    }

    public function listForUser(string $userIdentifier): array
    {
        $user = $this->requireUser($userIdentifier);

        $statement = $this->db->prepare(
            "SELECT event_id
             FROM event_registrations
             WHERE user_id = ?
             AND status = 'registered'
             ORDER BY registered_at DESC"
        );
        $statement->execute([$user['id']]);

        $events = [];
        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $eventId) {
            $event = $this->events->find((int)$eventId);
            if ($event) {
                $event['isRegistered'] = true;
                $events[] = $event;
            }
        }

        return $events;
    }

    public function isRegistered(string $userIdentifier, int $eventId): bool
    {
        $user = $this->requireUser($userIdentifier);

        $statement = $this->db->prepare(
            "SELECT COUNT(*)
             FROM event_registrations
             WHERE user_id = ?
             AND event_id = ?
             AND status = 'registered'"
        );
        $statement->execute([$user['id'], $eventId]);

        return (int)$statement->fetchColumn() > 0;
    }

    public function register(string $userIdentifier, int $eventId): array
    {
        $user = $this->requireUser($userIdentifier);
        $event = $this->requireApprovedEvent($eventId);

        $statement = $this->db->prepare(
            "INSERT INTO event_registrations (user_id, event_id, status, registered_at, cancelled_at)
             VALUES (?, ?, 'registered', NOW(), NULL)
             ON DUPLICATE KEY UPDATE status = 'registered', registered_at = NOW(), cancelled_at = NULL"
        );
        $statement->execute([$user['id'], $eventId]);

        $event['isRegistered'] = true;
        return $event;
    }

    public function unregister(string $userIdentifier, int $eventId): array
    {
        $user = $this->requireUser($userIdentifier);
        $event = $this->events->find($eventId);

        if (!$event) {
            throw new \RuntimeException('Event not found.');
        }

        $statement = $this->db->prepare(
            "UPDATE event_registrations
             SET status = 'cancelled', cancelled_at = NOW()
             WHERE user_id = ?
             AND event_id = ?"
        );
        $statement->execute([$user['id'], $eventId]);

        $event['isRegistered'] = false;
        return $event;
    }

    private function requireUser(string $identifier): array
    {
        $user = $this->users->findByIdentifier($identifier);

        if (!$user) {
            throw new \InvalidArgumentException(json_encode(['user' => 'User not found.']));
        }

        return $user;
    }

    private function requireApprovedEvent(int $eventId): array
    {
        $event = $this->events->find($eventId);

        if (!$event) {
            throw new \RuntimeException('Event not found.');
        }

        if ($event['status'] !== 'Approved') {
            throw new \InvalidArgumentException(json_encode(['event' => 'Only approved events can be registered.']));
        }

        return $event;
    }
}
