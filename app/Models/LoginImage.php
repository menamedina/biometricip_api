<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginImage extends Model
{
    protected $table      = 'tbl_login_images';
    protected $connection = 'mysql';

    protected $fillable = [
        'titulo',
        'imagen',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public static function forLogin()
    {
        return static::where('activo', true)
            ->orderBy('orden')
            ->get();
    }
}
