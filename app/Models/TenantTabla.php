<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantTabla extends Model
{
    protected $connection = 'mysql';
    protected $table      = 'tbl_admin_tenant';

    protected $fillable = [
        'nombre_tabla',
        'descripcion',
        'es_bd_central',
        'copiar_estructura',
        'copiar_datos',
        'activo',
        'orden',
    ];

    protected $casts = [
        'es_bd_central'     => 'boolean',
        'copiar_estructura' => 'boolean',
        'copiar_datos'      => 'boolean',
        'activo'            => 'boolean',
        'orden'             => 'integer',
    ];

    /** Devuelve nombres de tablas para generar SQL de estructura */
    public static function getTablasEstructura(): array
    {
        return self::where('es_bd_central', false)
            ->where('copiar_estructura', true)
            ->where('activo', true)
            ->orderBy('orden')
            ->pluck('nombre_tabla')
            ->toArray();
    }

    /** Devuelve nombres de tablas que además copian datos */
    public static function getTablasDatos(): array
    {
        return self::where('es_bd_central', false)
            ->where('copiar_datos', true)
            ->where('activo', true)
            ->orderBy('orden')
            ->pluck('nombre_tabla')
            ->toArray();
    }
}
