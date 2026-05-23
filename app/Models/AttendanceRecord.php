<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    protected $fillable = [
        'empleado_id',
        'sede_id',
        'tipo',
        'lat',
        'lng',
        'foto_evidencia',
        'metodo',
        'qr_validado',
        'geocerca_validada',
        'distancia_oficina_mts',
        'notas',
        'fecha_hora',
    ];

    protected function casts(): array
    {
        return [
            'qr_validado' => 'boolean',
            'geocerca_validada' => 'boolean',
            'distancia_oficina_mts' => 'float',
            'fecha_hora' => 'datetime',
        ];
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function photo(): HasOne
    {
        return $this->hasOne(AttendancePhoto::class);
    }
}
