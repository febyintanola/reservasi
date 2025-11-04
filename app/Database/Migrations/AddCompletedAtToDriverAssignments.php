<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompletedAtToDriverAssignments extends Migration
{
    public function up()
    {
        $fields = [
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addColumn('driver_assignments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('driver_assignments', 'completed_at');
    }
}
