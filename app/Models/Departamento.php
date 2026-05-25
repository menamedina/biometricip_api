<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Departamento extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_departamentos';

    protected $fillable = ['nombre', 'descripcion', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
