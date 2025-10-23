<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'        => 'ITE 311 - Web Systems & Design',
                'description'  => 'Introduction to web development concepts.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 312 - Information Management',
                'description'  => 'Covers database systems and applications.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 313 - Networking',
                'description'  => 'Principles of Hardware.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('courses')->insertBatch($data);
    }
}
