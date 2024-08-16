<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissionRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'user_type' => [
                'type' => 'ENUM',
                'constraint' => ['siswa', 'guru'],
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default' => 'pending',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('permission_requests');
    }

    public function down()
    {
        $this->forge->dropTable('permission_requests');
    }
}
