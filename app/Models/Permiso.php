<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permiso extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_permisos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha'         => 'date',
            'horas_permiso' => 'decimal:2',
            'fecha_inicio'  => 'datetime',
            'fecha_fin'     => 'datetime',
        ];
    }

    public function tipoPermiso(): BelongsTo
    {
        return $this->belongsTo(TipoPermiso::class, 'tipo_permiso_id');
    }
}
