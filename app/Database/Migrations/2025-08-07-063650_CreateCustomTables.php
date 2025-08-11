<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomTables extends Migration
{
    public function up()
    {
        // TABLE: users (custom user info, not Shield's)
        $this->forge->addField([
    'id'         => ['type' => 'INT', 'auto_increment' => true, 'unsigned' => true],
    'user_id'    => ['type' => 'INT', 'unsigned' => true],
    'nama'       => ['type' => 'VARCHAR', 'constraint' => 100],
    'divisi'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
    'no_tlp'     => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
    'foto_url'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
    'created_at' => ['type' => 'DATETIME', 'null' => true],
    'updated_at' => ['type' => 'DATETIME', 'null' => true],
]);
$this->forge->addKey('id', true);
$this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
$this->forge->createTable('user_profile');


        // TABLE: car_bookings
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'INT', 'unsigned' => true],
            'nama'             => ['type' => 'VARCHAR', 'constraint' => 255],
            'tanggal_pergi'    => ['type' => 'DATE'],
            'tanggal_pulang'   => ['type' => 'DATE'],
            'pengikut'         => ['type' => 'VARCHAR', 'constraint' => 255],
            'tujuan'           => ['type' => 'VARCHAR', 'constraint' => 255],
            'jumlah_hari'      => ['type' => 'INT'],
            'keperluan'        => ['type' => 'ENUM', 'constraint' => ['Dinas', 'Proyek']],
            'nama_pekerjaan'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'project_costing'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'task_number'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'expenditure_type' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'expenditure_org'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted'], 'default' => 'pending'],
           'created_at' => ['type' => 'DATETIME', 'null' => true],

        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('car_bookings');

        // TABLE: rooms
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_ruangan'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'lokasi'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'kapasitas'      => ['type' => 'INT'],
            'ruangrapat_url' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('rooms');

        // TABLE: room_bookings
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'room_id'      => ['type' => 'INT', 'unsigned' => true],
            'tanggal'      => ['type' => 'DATE'],
            'jam_mulai'    => ['type' => 'TIME'],
            'jam_selesai'  => ['type' => 'TIME'],
            'peserta'      => ['type' => 'INT'],
            'acara'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'Procost'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'Task'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'exptype'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted'], 'default' => 'pending'],
            'kebutuhan'    => ['type' => 'TEXT', 'null' => true],
            'keterangan'   => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],

        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('room_id', 'rooms', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('room_bookings');
    }

    public function down()
    {
        $this->forge->dropTable('room_bookings');
        $this->forge->dropTable('rooms');
        $this->forge->dropTable('car_bookings');
        $this->forge->dropTable('users');
    }
}
