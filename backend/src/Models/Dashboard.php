<?php

namespace Eventilize\Models;

use PDO;

class Dashboard
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function adminStats(): array
    {
        return [
            'totalUsers' => (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'totalEvents' => (int)$this->db->query('SELECT COUNT(*) FROM events')->fetchColumn(),
            'pending' => $this->countEventsByStatus('pending'),
            'approved' => $this->countEventsByStatus('approved'),
            'rejected' => $this->countEventsByStatus('rejected'),
        ];
    }

    private function countEventsByStatus(string $status): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM events WHERE status = ?');
        $statement->execute([$status]);

        return (int)$statement->fetchColumn();
    }
}
