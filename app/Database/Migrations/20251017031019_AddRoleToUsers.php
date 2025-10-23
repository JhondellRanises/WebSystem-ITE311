<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('role', 'users')) {
            $fields = [
                'role' => ['type' => "VARCHAR", 'constraint' => 50, 'null' => false, 'default' => 'student'],
            ];
            $this->forge->addColumn('users', $fields);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->fieldExists('role', 'users')) {
            $this->forge->dropColumn('users', 'role');
        }
    }
}
