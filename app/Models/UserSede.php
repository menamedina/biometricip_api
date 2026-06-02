<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSede extends Model
{
    protected $connection = 'mysql';
    protected $table = 'tbl_user_sedes';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'sede_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id'    => 'integer',
            'empresa_id' => 'integer',
            'sede_id'    => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
