<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Nomina extends Model
{
    use HasUuids;

    protected $fillable = [
        'periodo_id', 'user_id', 'estado',
        'con_licencia', 'observacion_licencia', 'plazo_licencia', 'documento_licencia',
        'observacion_secretario',
    ];

    protected function casts(): array
    {
        return [
            'con_licencia'   => 'boolean',
            'plazo_licencia' => 'date',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    public function puedeCargarEvidencias(): bool
    {
        return in_array($this->estado, ['pendiente', 'en_carga']);
    }

    public function estaEnEvaluacion(): bool
    {
        return $this->estado === 'en_evaluacion';
    }

    // ── Relaciones ───────────────────────────────────────────────────────
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function academico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class);
    }

    public function evidenciasNormales(): HasMany
    {
        return $this->hasMany(Evidencia::class)->where('es_apelacion', false);
    }

    public function evidenciasApelacion(): HasMany
    {
        return $this->hasMany(Evidencia::class)->where('es_apelacion', true);
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class);
    }

    public function calificacionFinal(): HasOne
    {
        return $this->hasOne(CalificacionFinal::class)
            ->whereRaw(
                '"calificaciones_finales"."id" = (
                    SELECT cf.id
                    FROM calificaciones_finales cf
                    WHERE cf.nomina_id = "calificaciones_finales"."nomina_id"
                    ORDER BY cf.created_at DESC
                    LIMIT 1
                )'
            );
    }

    public function calificacionJefatura(): HasOne
    {
        return $this->hasOne(CalificacionJefatura::class);
    }

    public function apelacion(): HasOne
    {
        return $this->hasOne(Apelacion::class)
            ->whereRaw(
                '"apelaciones"."id" = (
                    SELECT a.id
                    FROM apelaciones a
                    WHERE a.nomina_id = "apelaciones"."nomina_id"
                    ORDER BY a.created_at DESC
                    LIMIT 1
                )'
            );
    }

    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    public function solicitudActiva(string $tipo = 'licencia_medica'): ?Solicitud
    {
        return $this->solicitudes()
            ->where('tipo', $tipo)
            ->where('estado', 'activa')
            ->latest()
            ->first();
    }

    public function tieneLicenciaMedicaActiva(): bool
    {
        return $this->solicitudActiva('licencia_medica') !== null;
    }

    public function estadoReporte(): string
    {
        $licencia = $this->solicitudActiva('licencia_medica');
        if ($licencia) {
            $hasta = $licencia->fecha_fin?->format('d/m/Y') ?? 'indefinido';

            return "Pendiente - Licencia hasta {$hasta}";
        }

        $pendiente = $this->solicitudes()
            ->where('tipo', 'licencia_medica')
            ->where('estado', 'pendiente_aprobacion')
            ->latest()
            ->first();

        if ($pendiente) {
            return 'Pendiente - Licencia (aprobación CCDA)';
        }

        if (in_array($this->estado, ['evaluado', 'cerrado'])) {
            return 'Evaluado';
        }

        return 'Sin evaluar';
    }

    public function tieneSolicitudLicenciaPendiente(): bool
    {
        return $this->solicitudes()
            ->where('tipo', 'licencia_medica')
            ->where('estado', 'pendiente_aprobacion')
            ->exists();
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeDeEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }
}