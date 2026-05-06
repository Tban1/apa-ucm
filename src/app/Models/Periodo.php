<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Periodo extends Model
{
    use HasUuids;

    protected $fillable = [
        'anio', 'nombre', 'estado',
        'fecha_inicio', 'fecha_cierre', 'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio'  => 'date',
            'fecha_cierre'  => 'date',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    public function estaActivo(): bool   { return $this->estado === 'activo'; }
    public function estaCerrado(): bool  { return $this->estado === 'cerrado'; }

    // ── Relaciones ───────────────────────────────────────────────────────
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function cronogramas(): HasMany
    {
        return $this->hasMany(Cronograma::class);
    }

    public function nominas(): HasMany
    {
        return $this->hasMany(Nomina::class);
    }

    public function actas(): HasMany
    {
        return $this->hasMany(Acta::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}