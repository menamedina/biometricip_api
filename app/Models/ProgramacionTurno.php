<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramacionTurno extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_programacion_turno';

    protected $fillable = [
        'lider_id',
        'user_id',
        'horario_id',
        'semana_inicio',
    ];

    protected function casts(): array
    {
        return [
            'semana_inicio' => 'date',
        ];
    }
}
