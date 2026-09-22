<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Capacitacion extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_capacitaciones';

    protected $fillable = [
        'titulo',
        'tipo_evento',
        'temas',
        'metodologia',
        'impacto_medible',
        'indicador_nombre',
        'formula_indicador',
        'frecuencia_medicion',
        'observaciones',
        'fechas_sesiones',
        'instructor_nombre',
        'fecha_capacitacion',
        'duracion_horas',
        'token',
        'expira_en',
        'fecha_expiracion',
        'activo',
        'cerrada',
        'creado_por',
        'empresa_id',
    ];

    protected function casts(): array
    {
        return [
            'temas'              => 'array',
            'impacto_medible'    => 'boolean',
            'fechas_sesiones'    => 'array',
            'fecha_capacitacion' => 'datetime',
            'fecha_expiracion'   => 'datetime',
            'activo'             => 'boolean',
            'cerrada'            => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->token = Str::random(64);
            }
        });
    }

    public function asistentes(): HasMany
    {
        return $this->hasMany(CapacitacionAsistente::class, 'capacitacion_id');
    }

    public function estaVigente(): bool
    {
        return $this->activo && now()->lt($this->fecha_expiracion);
    }

    /**
     * El link de registro está disponible para asistentes:
     * activo + no cerrada manualmente + link no expirado
     */
    public function aceptaRegistros(): bool
    {
        if (!$this->activo || $this->cerrada) return false;
        if ($this->sinExpiracion())           return true;
        return now()->lt($this->fecha_expiracion);
    }

    /**
     * Estado legible para el admin:
     * Desactivada > Cerrada > Expirada > Abierta
     */
    public function sinExpiracion(): bool
    {
        return $this->expira_en === 0;
    }

    public function estadoLabel(): string
    {
        if (!$this->activo)                                          return 'Desactivada';
        if ($this->cerrada)                                          return 'Cerrada';
        if (!$this->sinExpiracion() && now()->gte($this->fecha_expiracion)) return 'Expirada';
        return 'Abierta';
    }
}
