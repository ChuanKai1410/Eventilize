<?php

namespace Eventilize\Models;

use PDO;

class User
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function register(array $payload): array
    {
        $data = $this->normalize($payload, true);
        $this->validate($data, true);

        if ($this->findByEmail($data['email'])) {
            throw new \InvalidArgumentException(json_encode(['email' => 'An account with this email already exists.']));
        }

        $statement = $this->db->prepare(
            'INSERT INTO users (name, email, password_hash, role)
             VALUES (?, ?, ?, ?)'
        );
        $statement->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
        ]);

        return $this->findById((int)$this->db->lastInsertId());
    }

    public function login(string $email, string $password): array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $statement->execute([strtolower(trim($email))]);
        $row = $statement->fetch();

        if (!$row || !password_verify($password, $row['password_hash'])) {
            throw new \InvalidArgumentException(json_encode(['credentials' => 'Invalid email or password.']));
        }

        return $this->transform($row);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE user_id = ?');
        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row ? $this->transform($row) : null;
    }

    public function findByIdentifier(string $identifier): ?array
    {
        if (is_numeric($identifier)) {
            return $this->findById((int)$identifier);
        }

        return $this->findByEmail($identifier);
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $statement->execute([strtolower(trim($email))]);
        $row = $statement->fetch();

        return $row ? $this->transform($row) : null;
    }

    public function count(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    private function normalize(array $payload, bool $includePassword): array
    {
        $data = [
            'name' => trim((string)($payload['name'] ?? '')),
            'email' => strtolower(trim((string)($payload['email'] ?? ''))),
            'role' => trim((string)($payload['role'] ?? 'student')),
        ];

        if ($includePassword) {
            $data['password'] = (string)($payload['password'] ?? '');
        }

        return $data;
    }

    private function validate(array $data, bool $includePassword): void
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Full name is required.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (!in_array($data['role'], ['student', 'organizer', 'admin'], true)) {
            $errors['role'] = 'Invalid role.';
        }

        if ($includePassword && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($errors) {
            throw new \InvalidArgumentException(json_encode($errors));
        }
    }

    private function transform(array $row): array
    {
        return [
            'id' => (int)$row['user_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'organizerName' => $row['role'] === 'organizer' ? $row['name'] : null,
            'createdAt' => $row['created_at'] ?? null,
            'updatedAt' => $row['updated_at'] ?? null,
        ];
    }
}
