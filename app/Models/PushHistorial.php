<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushHistorial extends Model
{
    protected $table = 'tbl_push_historial';

    public $timestamps = false;

    protected $fillable = [
        'empresa_id',
        'enviado_por',
        'titulo',
        'mensaje',
        'tipo_destinatario',
        'lider_id',
        'user_ids',
        'total_enviados',
        'total_exitosos',
        'total_fallidos',
        'created_at',
    ];

    protected $casts = [
        'user_ids'   => 'array',
        'created_at' => 'datetime',
    ];
}
