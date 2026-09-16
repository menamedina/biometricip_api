<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapacitacionAsistente extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_capacitacion_asistentes';

    public $timestamps = false;

    protected $fillable = [
        'capacitacion_id',
        'cedula',
        'nombre',
        'correo',
        'telefono',
        'estado',
        'fecha_confirmacion',
        'ip_registro',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at'         => 'datetime',
            'fecha_confirmacion' => 'datetime',
        ];
    }

    public function esProgramado(): bool
    {
        return $this->estado === 'programado';
    }

    public function esConfirmado(): bool
    {
        return $this->estado === 'confirmado';
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'capacitacion_id');
    }
}
