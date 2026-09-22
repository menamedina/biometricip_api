<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoPermiso extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_tipos_permiso';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'es_remunerado' => 'boolean',
            'is_active'     => 'boolean',
        ];
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'tipo_permiso_id');
    }
}
