<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_registros_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('tbl_empleados')->cascadeOnDelete();
            $table->foreignId('sede_id')->constrained('tbl_sedes')->cascadeOnDelete();
            $table->enum('tipo', ['entrada', 'salida_almuerzo', 'regreso_almuerzo', 'salida']);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('foto_evidencia')->nullable();
            $table->enum('metodo', ['qr', 'biometrico', 'reconocimiento_facial']);
            $table->boolean('qr_validado')->default(false);
            $table->boolean('geocerca_validada')->default(false);
            $table->decimal('distancia_oficina_mts', 8, 2)->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('fecha_hora');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_registros_asistencia');
    }
};
