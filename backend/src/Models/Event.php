<?php

namespace Eventilize\Models;

use PDO;

class Event
{
    private PDO $db;

    private array $statusToDatabase = [
        'draft' => 'draft',
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'Draft' => 'draft',
        'Pending' => 'pending',
        'Approved' => 'approved',
        'Rejected' => 'rejected',
    ];

    private array $statusToFrontend = [
        'draft' => 'Draft',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(array $filters = []): array
    {
        $sql = $this->baseSelect() . ' WHERE 1 = 1';
        $params = [];

        if (!empty($filters['status']) && isset($this->statusToDatabase[$filters['status']])) {
            $sql .= ' AND e.status = ?';
            $params[] = $this->statusToDatabase[$filters['status']];
        }

        if (!empty($filters['category'])) {
            $sql .= ' AND c.category_name = ?';
            $params[] = $filters['category'];
        }

        if (!empty($filters['organizer'])) {
            $sql .= ' AND u.name = ?';
            $params[] = $filters['organizer'];
        }

        if (!empty($filters['q'])) {
            $sql .= ' AND (e.title LIKE ? OR e.description LIKE ? OR c.category_name LIKE ? OR u.name LIKE ?)';
            $keyword = '%' . $filters['q'] . '%';
            array_push($params, $keyword, $keyword, $keyword, $keyword);
        }

        $sql .= ' ORDER BY e.event_date ASC, e.start_time ASC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return array_map(fn ($row) => $this->transform($row), $statement->fetchAll());
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare($this->baseSelect() . ' WHERE e.event_id = ?');
        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row ? $this->transform($row) : null;
    }

    public function create(array $payload): array
    {
        $data = $this->normalizePayload($payload);
        $this->validate($data);

        $this->db->beginTransaction();

        try {
            $categoryId = $this->resolveCategoryId($data['category']);
            $organizerId = $this->resolveOrganizerId($data['organizer']);

            $statement = $this->db->prepare(
                'INSERT INTO events
                    (title, description, category_id, organizer_id, event_date, start_time, end_time, location,
                     map_link, registration_link, event_image, poster_images, status, reject_reason, status_updated_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );
            $statement->execute([
                $data['title'],
                $data['description'],
                $categoryId,
                $organizerId,
                $data['eventDate'],
                $this->normalizeTime($data['startTime']),
                $this->normalizeTime($data['endTime']),
                $data['location'],
                $data['mapLink'] ?: null,
                $data['registrationLink'] ?: null,
                $data['eventImage'] ?: null,
                json_encode($data['poster'], JSON_UNESCAPED_SLASHES),
                $data['status'],
                $data['rejectReason'] ?: null,
            ]);

            $eventId = (int)$this->db->lastInsertId();
            $this->db->prepare('INSERT INTO event_analytics (event_id, views_count, bookmarks_count) VALUES (?, 0, 0)')
                ->execute([$eventId]);

            $this->db->commit();

            return $this->find($eventId);
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function update(int $id, array $payload): ?array
    {
        if (!$this->find($id)) {
            return null;
        }

        $data = $this->normalizePayload($payload);
        $this->validate($data);

        $categoryId = $this->resolveCategoryId($data['category']);

        $statement = $this->db->prepare(
            'UPDATE events
             SET title = ?, description = ?, category_id = ?, event_date = ?, start_time = ?, end_time = ?,
                 location = ?, map_link = ?, registration_link = ?, event_image = ?, poster_images = ?,
                 status = ?, reject_reason = ?, status_updated_at = NOW()
             WHERE event_id = ?'
        );
        $statement->execute([
            $data['title'],
            $data['description'],
            $categoryId,
            $data['eventDate'],
            $this->normalizeTime($data['startTime']),
            $this->normalizeTime($data['endTime']),
            $data['location'],
            $data['mapLink'] ?: null,
            $data['registrationLink'] ?: null,
            $data['eventImage'] ?: null,
            json_encode($data['poster'], JSON_UNESCAPED_SLASHES),
            $data['status'],
            $data['rejectReason'] ?: null,
            $id,
        ]);

        return $this->find($id);
    }

    public function delete(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $this->db->beginTransaction();

        try {
            $this->db->prepare('DELETE FROM bookmarks WHERE event_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM event_registrations WHERE event_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM notifications WHERE event_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM event_analytics WHERE event_id = ?')->execute([$id]);
            $this->db->prepare('DELETE FROM events WHERE event_id = ?')->execute([$id]);
            $this->db->commit();

            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function updateStatus(int $id, string $status, string $rejectReason = ''): ?array
    {
        if (!$this->find($id)) {
            return null;
        }

        if (!isset($this->statusToDatabase[$status])) {
            throw new \InvalidArgumentException('Invalid status value.');
        }

        $dbStatus = $this->statusToDatabase[$status];

        if ($dbStatus === 'rejected' && trim($rejectReason) === '') {
            throw new \InvalidArgumentException('Reject reason is required.');
        }

        $statement = $this->db->prepare(
            'UPDATE events
             SET status = ?, reject_reason = ?, status_updated_at = NOW()
             WHERE event_id = ?'
        );
        $statement->execute([
            $dbStatus,
            $dbStatus === 'rejected' ? trim($rejectReason) : null,
            $id,
        ]);

        return $this->find($id);
    }

    public function incrementViews(int $id): ?array
    {
        if (!$this->find($id)) {
            return null;
        }

        $this->db->prepare(
            'INSERT INTO event_analytics (event_id, views_count, bookmarks_count)
             VALUES (?, 1, 0)
             ON DUPLICATE KEY UPDATE views_count = views_count + 1'
        )->execute([$id]);

        return $this->find($id);
    }

    public function toggleBookmark(int $id, ?string $userIdentifier): ?array
    {
        if (!$this->find($id)) {
            return null;
        }

        $userId = $this->resolveBookmarkUserId($userIdentifier);

        $statement = $this->db->prepare('SELECT bookmark_id FROM bookmarks WHERE user_id = ? AND event_id = ?');
        $statement->execute([$userId, $id]);
        $bookmark = $statement->fetch();

        if ($bookmark) {
            $this->db->prepare('DELETE FROM bookmarks WHERE bookmark_id = ?')->execute([$bookmark['bookmark_id']]);
            $isBookmarked = false;
        } else {
            $this->db->prepare('INSERT INTO bookmarks (user_id, event_id) VALUES (?, ?)')->execute([$userId, $id]);
            $isBookmarked = true;
        }

        $count = $this->countBookmarks($id);
        $this->db->prepare(
            'INSERT INTO event_analytics (event_id, views_count, bookmarks_count)
             VALUES (?, 0, ?)
             ON DUPLICATE KEY UPDATE bookmarks_count = ?'
        )->execute([$id, $count, $count]);

        $event = $this->find($id);
        $event['isBookmarked'] = $isBookmarked;

        return $event;
    }

    public function categories(): array
    {
        return $this->db->query('SELECT category_name FROM categories ORDER BY category_name ASC')->fetchAll(PDO::FETCH_COLUMN);
    }

    private function baseSelect(): string
    {
        return "SELECT e.*, c.category_name, u.name AS organizer_name,
                       COALESCE(ea.views_count, 0) AS views_count,
                       COALESCE(ea.bookmarks_count, 0) AS bookmarks_count
                FROM events e
                JOIN categories c ON e.category_id = c.category_id
                JOIN users u ON e.organizer_id = u.user_id
                LEFT JOIN event_analytics ea ON e.event_id = ea.event_id";
    }

    private function transform(array $row): array
    {
        $poster = [];
        if (!empty($row['poster_images'])) {
            $decoded = json_decode($row['poster_images'], true);
            $poster = is_array($decoded) ? $decoded : [];
        }

        return [
            'id' => (int)$row['event_id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category_name'],
            'categoryId' => (int)$row['category_id'],
            'organizer' => $row['organizer_name'],
            'organizerId' => (int)$row['organizer_id'],
            'eventDate' => $row['event_date'],
            'startTime' => substr($row['start_time'], 0, 5),
            'endTime' => substr($row['end_time'], 0, 5),
            'location' => $row['location'],
            'mapLink' => $row['map_link'] ?? '',
            'registrationLink' => $row['registration_link'] ?? '',
            'eventImage' => $row['event_image'] ?? '',
            'poster' => $poster,
            'status' => $this->statusToFrontend[$row['status']] ?? 'Pending',
            'rejectReason' => $row['reject_reason'] ?? '',
            'statusUpdatedAt' => $row['status_updated_at'] ?? $row['updated_at'],
            'viewsCount' => (int)$row['views_count'],
            'bookmarksCount' => (int)$row['bookmarks_count'],
            'isBookmarked' => false,
            'createdAt' => $row['created_at'],
            'updatedAt' => $row['updated_at'],
        ];
    }

    private function normalizePayload(array $payload): array
    {
        $status = $payload['status'] ?? 'Pending';

        return [
            'title' => trim((string)($payload['title'] ?? '')),
            'description' => trim((string)($payload['description'] ?? '')),
            'category' => trim((string)($payload['category'] ?? '')),
            'organizer' => trim((string)($payload['organizer'] ?? 'Computing Students Society')),
            'eventDate' => trim((string)($payload['eventDate'] ?? '')),
            'startTime' => trim((string)($payload['startTime'] ?? '')),
            'endTime' => trim((string)($payload['endTime'] ?? '')),
            'location' => trim((string)($payload['location'] ?? '')),
            'mapLink' => trim((string)($payload['mapLink'] ?? '')),
            'registrationLink' => trim((string)($payload['registrationLink'] ?? '')),
            'eventImage' => (string)($payload['eventImage'] ?? ''),
            'poster' => is_array($payload['poster'] ?? null) ? $payload['poster'] : [],
            'status' => $this->statusToDatabase[$status] ?? '',
            'rejectReason' => trim((string)($payload['rejectReason'] ?? '')),
        ];
    }

    private function validate(array $data): void
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = 'Event title is required.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'Description is required.';
        }

        if ($data['category'] === '') {
            $errors['category'] = 'Category is required.';
        }

        $date = \DateTime::createFromFormat('Y-m-d', $data['eventDate']);
        if (!$date || $date->format('Y-m-d') !== $data['eventDate']) {
            $errors['eventDate'] = 'Event date must be a valid date.';
        }

        if (!$this->isTime($data['startTime'])) {
            $errors['startTime'] = 'Start time is required.';
        }

        if (!$this->isTime($data['endTime'])) {
            $errors['endTime'] = 'End time is required.';
        }

        if ($this->isTime($data['startTime']) && $this->isTime($data['endTime']) && $data['endTime'] < $data['startTime']) {
            $errors['endTime'] = 'End time must be after start time.';
        }

        if ($data['location'] === '') {
            $errors['location'] = 'Location is required.';
        }

        if ($data['status'] === '') {
            $errors['status'] = 'Invalid status value.';
        }

        if ($data['status'] === 'rejected' && $data['rejectReason'] === '') {
            $errors['rejectReason'] = 'Reject reason is required.';
        }

        foreach (['mapLink', 'registrationLink'] as $field) {
            if ($data[$field] !== '' && !filter_var($data[$field], FILTER_VALIDATE_URL)) {
                $errors[$field] = 'URL must be valid.';
            }
        }

        if ($errors) {
            throw new \InvalidArgumentException(json_encode($errors));
        }
    }

    private function resolveCategoryId(string $categoryName): int
    {
        $statement = $this->db->prepare('SELECT category_id FROM categories WHERE category_name = ?');
        $statement->execute([$categoryName]);
        $categoryId = $statement->fetchColumn();

        if ($categoryId) {
            return (int)$categoryId;
        }

        $statement = $this->db->prepare('INSERT INTO categories (category_name) VALUES (?)');
        $statement->execute([$categoryName]);

        return (int)$this->db->lastInsertId();
    }

    private function resolveOrganizerId(string $organizerName): int
    {
        $statement = $this->db->prepare("SELECT user_id FROM users WHERE name = ? AND role = 'organizer'");
        $statement->execute([$organizerName]);
        $organizerId = $statement->fetchColumn();

        if ($organizerId) {
            return (int)$organizerId;
        }

        $emailBase = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $organizerName));
        $email = trim($emailBase, '.') . '@eventilize.local';

        $statement = $this->db->prepare(
            "INSERT INTO users (name, email, password_hash, role)
             VALUES (?, ?, ?, 'organizer')"
        );
        $statement->execute([$organizerName, $email, password_hash(bin2hex(random_bytes(12)), PASSWORD_DEFAULT)]);

        return (int)$this->db->lastInsertId();
    }

    private function resolveBookmarkUserId(?string $identifier): int
    {
        if ($identifier) {
            if (is_numeric($identifier)) {
                $statement = $this->db->prepare('SELECT user_id FROM users WHERE user_id = ?');
                $statement->execute([(int)$identifier]);
            } else {
                $statement = $this->db->prepare("SELECT user_id FROM users WHERE email = ? OR name = ?");
                $statement->execute([$identifier, $identifier]);
            }
            $userId = $statement->fetchColumn();

            if ($userId) {
                return (int)$userId;
            }
        }

        $statement = $this->db->query("SELECT user_id FROM users WHERE role = 'student' ORDER BY user_id LIMIT 1");
        return (int)$statement->fetchColumn();
    }

    private function countBookmarks(int $id): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM bookmarks WHERE event_id = ?');
        $statement->execute([$id]);

        return (int)$statement->fetchColumn();
    }

    private function normalizeTime(string $time): string
    {
        return strlen($time) === 5 ? $time . ':00' : $time;
    }

    private function isTime(string $time): bool
    {
        return (bool)preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time);
    }
}
