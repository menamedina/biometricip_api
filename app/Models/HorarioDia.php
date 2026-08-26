<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioDia extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_horario_dias';
    protected $guarded    = [];
    public    $timestamps = false;

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
}
