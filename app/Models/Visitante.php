<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitante extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_visitantes';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'hora_entrada' => 'datetime',
            'hora_salida'  => 'datetime',
        ];
    }

    // Campos requeridos solo en entrada (en salida se busca por cédula)
    public static array $camposEntrada = ['nombre', 'cedula', 'telefono', 'eps', 'arl', 'persona_visita'];

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function imagenes(): HasMany
    {
        return $this->hasMany(VisitanteImagen::class);
    }
}
