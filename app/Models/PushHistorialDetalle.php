<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushHistorialDetalle extends Model
{
    protected $table = 'tbl_push_historial_detalle';

    public $timestamps = false;

    protected $fillable = [
        'historial_id',
        'user_id',
        'exitoso',
        'created_at',
    ];

    protected $casts = [
        'exitoso'    => 'boolean',
        'created_at' => 'datetime',
    ];
}
