<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispositivoBiometrico extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tbl_dispositivos_biometricos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'puerto'      => 'integer',
            'is_active'   => 'boolean',
            'ultima_sync' => 'datetime',
        ];
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class, 'dispositivo_id');
    }
}
