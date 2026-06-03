<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitanteImagen extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_visitantes_imagenes';

    protected $guarded = [];

    public function visitante(): BelongsTo
    {
        return $this->belongsTo(Visitante::class);
    }
}
