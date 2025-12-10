<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $currentYear = (int)date('Y');
        $academicYear = ($currentYear - 1) . '-' . $currentYear;
        
        $data = [
            // Department of Engineering and Technology - Teacher 1 (ID: 2)
            [
                'title'         => 'ITE 311 - Web Systems & Design',
                'description'   => 'Introduction to web development concepts including HTML, CSS, JavaScript, and modern web frameworks.',
                'course_code'   => 'ITE311',
                'instructor_id' => 2,
                'units'         => 3.0,
                'term'          => '1',
                'semester'      => '1st Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Engineering and Technology',
                'program'       => 'Bachelor of Science in Computer Engineering',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'title'         => 'ITE 312 - Information Management',
                'description'   => 'Covers database systems and applications, SQL, and data modeling.',
                'course_code'   => 'ITE312',
                'instructor_id' => 2,
                'units'         => 3.0,
                'term'          => '1',
                'semester'      => '1st Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Engineering and Technology',
                'program'       => 'Bachelor of Science in Computer Engineering',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'title'         => 'ITE 313 - Networking',
                'description'   => 'Principles of hardware and networking fundamentals, network protocols, and security.',
                'course_code'   => 'ITE313',
                'instructor_id' => 2,
                'units'         => 3.0,
                'term'          => '2',
                'semester'      => '1st Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Engineering and Technology',
                'program'       => 'Bachelor of Science in Electrical Engineering',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'title'         => 'ITE 323 - Advanced Database Systems',
                'description'   => 'Explores advanced database concepts such as optimization, distributed systems, and NoSQL databases.',
                'course_code'   => 'ITE323',
                'instructor_id' => 2,
                'units'         => 3.0,
                'term'          => '2',
                'semester'      => '2nd Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Engineering and Technology',
                'program'       => 'Bachelor of Science in Computer Engineering',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            // Department of Arts and Sciences - Teacher 2 (ID: 3)
            [
                'title'         => 'ITE 101 - Programming 1',
                'description'   => 'Introduction to programming concepts using a high-level language. Covers variables, control structures, and basic algorithms.',
                'course_code'   => 'ITE101',
                'instructor_id' => 3,
                'units'         => 3.0,
                'term'          => '1',
                'semester'      => '1st Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Arts and Sciences',
                'program'       => 'Bachelor of Science in Mathematics',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'title'         => 'ITE 102 - Programming 2',
                'description'   => 'Advanced programming techniques and object-oriented programming concepts.',
                'course_code'   => 'ITE102',
                'instructor_id' => 3,
                'units'         => 3.0,
                'term'          => '2',
                'semester'      => '1st Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Arts and Sciences',
                'program'       => 'Bachelor of Science in Mathematics',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            // Department of Engineering and Technology - Teacher 1 (ID: 2)
            [
                'title'         => 'ITE 321 - Platform Technology',
                'description'   => 'Study of operating systems, virtualization, and platform services.',
                'course_code'   => 'ITE321',
                'instructor_id' => 2,
                'units'         => 3.0,
                'term'          => '1',
                'semester'      => '2nd Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Engineering and Technology',
                'program'       => 'Bachelor of Science in Computer Engineering',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
            [
                'title'         => 'ITE 322 - System Architecture and Design',
                'description'   => 'Covers system design methodologies and architectural frameworks.',
                'course_code'   => 'ITE322',
                'instructor_id' => 2,
                'units'         => 3.0,
                'term'          => '2',
                'semester'      => '2nd Semester',
                'academic_year' => $academicYear,
                'department'    => 'Department of Engineering and Technology',
                'program'       => 'Bachelor of Science in Computer Engineering',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s')
            ],
        ];

        $this->db->table('courses')->insertBatch($data);
    }
}
