<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigRecargo extends Model
{
    protected $connection = 'tenant';
    protected $table      = 'tbl_config_recargos';

    protected $guarded = [];

    /**
     * Obtener un valor de configuración por clave.
     */
    public static function valor(string $clave, string $default = ''): string
    {
        $config = static::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }
}
