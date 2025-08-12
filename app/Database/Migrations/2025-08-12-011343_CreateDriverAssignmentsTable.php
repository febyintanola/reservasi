<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDriverAssignmentsTable extends Migration
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
            'car_booking_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'driver_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'mobil_jenis' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'mobil_plat' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('car_booking_id');
        $this->forge->addKey('driver_id');
        $this->forge->createTable('driver_assignments', true);
    }

    public function down()
    {
        $this->forge->dropTable('driver_assignments', true);
    }
}
