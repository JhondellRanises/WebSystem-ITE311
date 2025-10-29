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
                'description'  => 'Principles of hardware and networking fundamentals.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 101 - Programming 1',
                'description'  => 'Introduction to programming concepts using a high-level language.',
                'instructor_id'=> 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 102 - Programming 2',
                'description'  => 'Advanced programming techniques and object-oriented programming concepts.',
                'instructor_id'=> 1,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 321 - Platform Technology',
                'description'  => 'Study of operating systems, virtualization, and platform services.',
                'instructor_id'=> 3,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 322 - System Architecture and Design',
                'description'  => 'Covers system design methodologies and architectural frameworks.',
                'instructor_id'=> 3,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ],
            [
                'title'        => 'ITE 323 - Advanced Database Systems',
                'description'  => 'Explores advanced database concepts such as optimization and distributed systems.',
                'instructor_id'=> 2,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s')
            ]
        ];

        $this->db->table('courses')->insertBatch($data);
    }
}
