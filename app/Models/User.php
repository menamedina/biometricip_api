<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

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
        'empleador_id',
        'lider_id',
        'codigo_empleado',
        'departamento_id',
        'cargo_id',
        'horario_id',
        'telefono',
        'centro_costo',
        'ruta',
        'foto_url',
        'face_descriptor',
        'last_login_at',
        'otp_code',
        'otp_expires_at',
        'tratamiento_datos',
        'tratamiento_datos_at',
        'dias_tratamiento_dato',
        'ver_todos',
        'marcacion_global',
        'api_token',
        'scope_registros',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'admin_tenant'      => 'boolean',
            'empresa_id'        => 'integer',
            'face_descriptor'   => 'array',
            'tratamiento_datos'    => 'boolean',
            'tratamiento_datos_at' => 'datetime',
            'dias_tratamiento_dato' => 'integer',
            'ver_todos'             => 'boolean',
            'marcacion_global'      => 'boolean',
            'scope_registros'       => 'string',
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

    public function imagenPerfil(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserImagen::class, 'user_id')->select(['user_id', 'imagen_thumbnail']);
    }


    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Devuelve los IDs del equipo visible para este usuario según su scope_registros.
     * empresa  → todos los empleados activos de la empresa.
     * equipo   → sus subordinados directos + él mismo.
     * personal → solo su propio ID.
     */
    public function equipoIds(): array
    {
        $scope = $this->scope_registros ?? ($this->ver_todos ? 'empresa' : 'personal');

        if ($scope === 'empresa') {
            return User::where('empresa_id', $this->empresa_id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
        }

        if ($scope === 'equipo') {
            $equipo = User::where('lider_id', $this->id)
                ->where('empresa_id', $this->empresa_id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            return array_unique(array_merge($equipo, [$this->id]));
        }

        return [$this->id];
    }

    /**
     * Query de empleados visibles para este usuario (sin incluirse a sí mismo).
     */
    public function equipoQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::where('empresa_id', $this->empresa_id)->where('is_active', true);

        $scope = $this->scope_registros ?? ($this->ver_todos ? 'empresa' : 'personal');

        if ($scope !== 'empresa') {
            $query->where('lider_id', $this->id);
        }

        return $query;
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
