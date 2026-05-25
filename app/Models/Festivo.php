<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Festivo extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_festivos';

    protected $fillable = ['fecha', 'nombre', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
