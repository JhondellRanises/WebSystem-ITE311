<?php 

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // Admin (ID: 1)
            [
                'name'       => 'Admin User',
                'email'      => 'admin@example.com',
                'password'   => password_hash('123456', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Teachers (IDs: 2, 3, 4)
            [
                'name'       => 'Teacher 1',
                'email'      => 'teacher1@example.com',
                'password'   => password_hash('123456', PASSWORD_DEFAULT),
                'role'       => 'teacher',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Teacher 2',
                'email'      => 'teacher2@example.com',
                'password'   => password_hash('123456', PASSWORD_DEFAULT),
                'role'       => 'teacher',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // Students (IDs: 5, 6)
            [
                'name'       => 'Student 1',
                'email'      => 'student1@example.com',
                'password'   => password_hash('123456', PASSWORD_DEFAULT),
                'role'       => 'student',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name'       => 'Student 2',
                'email'      => 'student2@example.com',
                'password'   => password_hash('123456', PASSWORD_DEFAULT),
                'role'       => 'student',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert multiple users
        $this->db->table('users')->insertBatch($data);
    }
}
