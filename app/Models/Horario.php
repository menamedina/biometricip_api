<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_horarios';

    protected $fillable = [
        'nombre',
        'hora_entrada',
        'hora_salida',
        'duracion_almuerzo_min',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
