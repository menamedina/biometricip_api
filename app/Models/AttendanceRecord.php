<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AttendanceRecord extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_registros_asistencia';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'qr_validado' => 'boolean',
            'geocerca_validada' => 'boolean',
            'distancia_oficina_mts' => 'float',
            'fecha_hora' => 'datetime',
            'es_sincronizacion_offline' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }

    public function photo(): HasOne
    {
        return $this->hasOne(AttendancePhoto::class);
    }
}
