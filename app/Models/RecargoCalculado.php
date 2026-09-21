<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecargoCalculado extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_recargos_calculados';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'fecha'   => 'date',
            'minutos' => 'decimal:2',
        ];
    }

    public function conceptoRecargo(): BelongsTo
    {
        return $this->belongsTo(ConceptoRecargo::class, 'concepto_recargo_id');
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
}
