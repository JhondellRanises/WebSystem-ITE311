<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    // Table and primary key
    protected $table      = 'users';
    protected $primaryKey = 'id';

    // Allowed fields for insert/update
    protected $allowedFields = ['name', 'email', 'password', 'role', 'created_at', 'updated_at'];

    // Automatically manage created_at and updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // -----------------------------
    // Find a user by email
    // -----------------------------
    public function findUserByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    // -----------------------------
    // Create a new user account
    // -----------------------------
    public function createAccount(array $data)
    {
        // Hash password before storing
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user and return inserted ID
        return $this->insert($data);
    }

    // -----------------------------
    // Get dashboard statistics
    // -----------------------------
    public function getDashboardStats(string $role, int $userId): array
    {
        $stats = [
            'total_users' => 0,
            'total_projects' => 0,
            'total_notifications' => 0,
            'my_courses' => 0,
            'my_notifications' => 0,
        ];

        // Example: only admin can see total users
        if ($role === 'admin') {
            $stats['total_users'] = $this->countAllResults();
        }

        // Customize other stats according to your app logic
        // e.g., total_projects, my_courses, my_notifications

        return $stats;
    }
}
