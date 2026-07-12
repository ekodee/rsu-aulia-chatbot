<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mengubah ENUM untuk menambahkan 'pdf'
        DB::statement("ALTER TABLE knowledge_bases MODIFY COLUMN type ENUM('artikel', 'jadwal_dokter', 'pdf')");
    }

    public function down(): void
    {
        // Rollback jika diperlukan
        DB::statement("ALTER TABLE knowledge_bases MODIFY COLUMN type ENUM('artikel', 'jadwal_dokter')");
    }
};
