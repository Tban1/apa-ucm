<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cronograma extends Model
{
    use HasUuids;

    public const ETAPAS = [
        'carga_evidencias',
        'evaluacion_secretario',
        'evaluacion_cca',
        'apelaciones',
        'evaluacion_jefatura',
        'cierre',
    ];

    public const ETIQUETAS = [
        'carga_evidencias'      => 'Carga de Evidencias',
        'evaluacion_secretario' => 'Validación Secretario',
        'evaluacion_cca'        => 'Evaluación CCA',
        'apelaciones'           => 'Apelaciones',
        'evaluacion_jefatura'   => 'Evaluación Jefatura',
        'cierre'                => 'Cierre',
    ];

    protected $fillable = [
        'periodo_id', 'etapa', 'fecha_inicio', 'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
        ];
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    public function estaVigente(): bool
    {
        $hoy = now()->toDateString();
        return $this->fecha_inicio->toDateString() <= $hoy
            && $this->fecha_fin->toDateString() >= $hoy;
    }

    public function haTerminado(): bool
    {
        return $this->fecha_fin->toDateString() < now()->toDateString();
    }

    public function esFutura(): bool
    {
        return $this->fecha_inicio->isFuture();
    }

    /**
     * Calcula la fecha de inicio de una etapa según la lógica del proceso CAD.
     *
     * @param  array<string, string>  $finesPorEtapa  Mapa etapa => fecha_fin (Y-m-d)
     */
    public static function calcularFechaInicio(string $etapa, string $periodoInicio, array $finesPorEtapa): string
    {
        return match ($etapa) {
            'carga_evidencias', 'evaluacion_secretario', 'evaluacion_jefatura' => $periodoInicio,
            'evaluacion_cca'  => $finesPorEtapa['carga_evidencias'],
            'apelaciones'     => $finesPorEtapa['evaluacion_cca'],
            'cierre'          => $finesPorEtapa['apelaciones'],
            default           => throw new \InvalidArgumentException("Etapa desconocida: {$etapa}"),
        };
    }

    /**
     * @param  array<int, array{etapa: string, fecha_fin: string}>  $entradas
     * @return array<int, array{etapa: string, fecha_inicio: string, fecha_fin: string}>
     */
    public static function prepararParaGuardar(string $periodoInicio, array $entradas): array
    {
        $finesPorEtapa = collect($entradas)->pluck('fecha_fin', 'etapa')->all();

        return collect($entradas)->map(fn (array $entry) => [
            'etapa'        => $entry['etapa'],
            'fecha_inicio' => self::calcularFechaInicio($entry['etapa'], $periodoInicio, $finesPorEtapa),
            'fecha_fin'    => $entry['fecha_fin'],
        ])->all();
    }

    public static function etiqueta(string $etapa): string
    {
        return self::ETIQUETAS[$etapa] ?? $etapa;
    }

    // ── Relaciones ───────────────────────────────────────────────────────
    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────
    public function scopeVigentes($query)
    {
        $hoy = now()->toDateString();
        return $query->where('fecha_inicio', '<=', $hoy)
                     ->where('fecha_fin', '>=', $hoy);
    }

    public function scopeDeEtapa($query, string $etapa)
    {
        return $query->where('etapa', $etapa);
    }
}
