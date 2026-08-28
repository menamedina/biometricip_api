<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    // Conexión tenant para multi-tenancy
    protected $connection = 'tenant';
    protected $table      = 'tbl_horarios';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function dias()
    {
        return $this->hasMany(HorarioDia::class)->orderBy('dia_semana');
    }
}
