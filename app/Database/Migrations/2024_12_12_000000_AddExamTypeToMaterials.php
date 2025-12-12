<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExamTypeToMaterials extends Migration
{
    public function up()
    {
        $this->forge->addColumn('materials', [
            'exam_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Prelim', 'Midterm', 'Final'],
                'default'    => 'Prelim',
                'null'       => false,
                'comment'    => 'Exam type: Prelim, Midterm, or Final'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('materials', 'exam_type');
    }
}
