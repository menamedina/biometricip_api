<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserImagen extends Model
{
    protected $connection = 'mysql';
    protected $table      = 'users_imagenes';

    protected $fillable = [
        'user_id',
        'imagen_base64',
        'imagen_thumbnail',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
