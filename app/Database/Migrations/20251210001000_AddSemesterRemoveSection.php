<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSemesterRemoveSection extends Migration
{
    public function up()
    {
        // Add semester column
        $this->forge->addColumn('courses', [
            'semester' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'term',
            ],
        ]);

        // Drop section column if it exists
        if ($this->db->fieldExists('section', 'courses')) {
            $this->forge->dropColumn('courses', 'section');
        }
    }

    public function down()
    {
        // Re-add section column
        $this->forge->addColumn('courses', [
            'section' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'term',
            ],
        ]);

        // Drop semester column
        if ($this->db->fieldExists('semester', 'courses')) {
            $this->forge->dropColumn('courses', 'semester');
        }
    }
}

