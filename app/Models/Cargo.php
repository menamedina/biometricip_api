<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_cargos';

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];
}
