<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_sync_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'registros_nuevos' => 'integer',
            'registros_total'  => 'integer',
            'created_at'       => 'datetime',
        ];
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(DispositivoBiometrico::class, 'dispositivo_id');
    }
}
