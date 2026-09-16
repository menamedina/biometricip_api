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
        'nombre',
        'correo',
        'telefono',
        'ip_registro',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function capacitacion(): BelongsTo
    {
        return $this->belongsTo(Capacitacion::class, 'capacitacion_id');
    }
}
