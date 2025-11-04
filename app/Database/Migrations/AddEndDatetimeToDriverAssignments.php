<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEndDatetimeToDriverAssignments extends Migration
{
    public function up()
    {
        $fields = [
            'end_datetime' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('driver_assignments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('driver_assignments', 'end_datetime');
    }
}
