<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'cedula',
        'email',
        'password',
        'role',
        'tipo',
        'admin_tenant',
        'is_active',
        'empresa_id',
        'codigo_empleado',
        'departamento_id',
        'cargo_id',
        'horario_id',
        'telefono',
        'foto_url',
        'face_descriptor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'admin_tenant'      => 'boolean',
            'empresa_id'        => 'integer',
            'face_descriptor'   => 'array',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function userSedes(): HasMany
    {
        return $this->hasMany(UserSede::class, 'user_id');
    }


    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->empresa_id === null && $this->role === 'admin';
    }
}
