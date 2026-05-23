<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    protected $table = 'tbl_empleados';

    protected $fillable = [
        'user_id',
        'codigo_empleado',
        'departamento',
        'cargo',
        'telefono',
        'foto_url',
        'face_descriptor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'face_descriptor' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function getLastActionAttribute(): ?string
    {
        $last = $this->attendanceRecords()
            ->whereDate('fecha_hora', today())
            ->latest('fecha_hora')
            ->first();

        if (!$last) {
            return null;
        }

        return in_array($last->tipo, ['entrada', 'regreso_almuerzo']) ? 'in' : 'out';
    }
}
