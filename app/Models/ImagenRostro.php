<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImagenRostro extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_imagenes_rostro_usuario';

    protected $fillable = [
        'user_id',
        'imagen_base64',
        'descriptor',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'descriptor' => 'array',
            'orden'      => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
