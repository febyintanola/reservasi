<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDriversTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'foto_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->createTable('drivers', true);
    }

    public function down()
    {
        $this->forge->dropTable('drivers', true);
    }
}
