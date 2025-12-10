<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCourseFields extends Migration
{
    public function up()
    {
        // Add new fields
        $fields = [
            'course_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'title',
            ],
            'academic_year' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'instructor_id',
            ],
            'department' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'academic_year',
            ],
            'program' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'department',
            ],
        ];

        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['course_code', 'academic_year', 'department', 'program']);
    }
}

