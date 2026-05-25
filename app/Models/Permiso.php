<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        ];
    }
}
