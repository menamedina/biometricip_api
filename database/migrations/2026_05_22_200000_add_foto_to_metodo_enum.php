<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tbl_registros_asistencia MODIFY COLUMN metodo ENUM('qr','biometrico','reconocimiento_facial','foto') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tbl_registros_asistencia MODIFY COLUMN metodo ENUM('qr','biometrico','reconocimiento_facial') NOT NULL");
    }
};
