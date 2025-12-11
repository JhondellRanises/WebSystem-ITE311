<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddControlNumberToCourses extends Migration
{
    public function up()
    {
        $this->forge->addColumn('courses', [
            'control_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'course_code'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', 'control_number');
    }
}
