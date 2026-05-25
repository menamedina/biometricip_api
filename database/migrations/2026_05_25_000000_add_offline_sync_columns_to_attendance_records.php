<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_registros_asistencia', function (Blueprint $table) {
            $table->boolean('es_sincronizacion_offline')->default(false)->after('fecha_hora');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_registros_asistencia', function (Blueprint $table) {
            $table->dropColumn('es_sincronizacion_offline');
        });
    }
};
