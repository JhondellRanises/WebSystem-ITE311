<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExtraFieldsToCourses extends Migration
{
    public function up()
    {
        // Add new metadata columns to courses table.
        $fields = [
            'cn' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
                'after' => 'id',
            ],
            'subject_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'cn',
            ],
            'term' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'subject_code',
            ],
            'section' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'term',
            ],
            'units' => [
                'type' => 'DECIMAL',
                'constraint' => '4,1',
                'null' => true,
                'after' => 'section',
            ],
            'schedule' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'description',
            ],
        ];

        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['cn', 'subject_code', 'term', 'section', 'units', 'schedule']);
    }
}

