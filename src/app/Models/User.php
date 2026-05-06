<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password',
        'rut', 'telefono', 'facultad_id', 'departamento_id',
        'role', 'activo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'activo'            => 'boolean',
        ];
    }

    // ── Verificación de rol ───────────────────────────────────────────────
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool          { return $this->role === 'admin'; }
    public function isAnalistaCCDA(): bool   { return $this->role === 'analista_ccda'; }
    public function isSecretario(): bool     { return $this->role === 'secretario'; }
    public function isMiembroCCA(): bool     { return $this->role === 'miembro_cca'; }
    public function isJefeAcademico(): bool  { return $this->role === 'jefe_academico'; }
    public function isAcademico(): bool      { return $this->role === 'academico'; }

    // ── Relaciones ───────────────────────────────────────────────────────
    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function nominas(): HasMany
    {
        return $this->hasMany(Nomina::class);
    }

    public function periodosCreados(): HasMany
    {
        return $this->hasMany(Periodo::class, 'creado_por');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'evaluador_id');
    }

    public function calificacionesJefatura(): HasMany
    {
        return $this->hasMany(CalificacionJefatura::class, 'jefe_id');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeDeRol($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeDeFacultad($query, string $facultadId)
    {
        return $query->where('facultad_id', $facultadId);
    }
}
