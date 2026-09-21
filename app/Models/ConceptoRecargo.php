<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConceptoRecargo extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_conceptos_recargo';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'porcentaje'  => 'decimal:2',
            'es_extra'    => 'boolean',
            'es_nocturno' => 'boolean',
            'es_festivo'  => 'boolean',
            'is_active'   => 'boolean',
        ];
    }

    public function recargosCalculados(): HasMany
    {
        return $this->hasMany(RecargoCalculado::class, 'concepto_recargo_id');
    }
}
