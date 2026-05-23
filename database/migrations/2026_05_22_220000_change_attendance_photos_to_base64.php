<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tbl_fotos_asistencia');

        Schema::create('tbl_fotos_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_record_id')->constrained('tbl_registros_asistencia')->cascadeOnDelete();
            $table->longText('foto_base64');
            $table->text('thumbnail_base64');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_fotos_asistencia');
    }
};
