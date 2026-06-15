<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompromisoApa extends Model
{
    use HasUuids;

    protected $table = 'compromisos_apa';

    protected $fillable = [
        'nomina_id', 'periodo_id', 'semestre',
        'pct_docencia', 'pct_investigacion', 'pct_extension',
        'pct_administracion', 'pct_otras',
        'hrs_docencia', 'hrs_investigacion', 'hrs_extension',
        'hrs_administracion', 'hrs_otras',
        'fuente', 'confirmado_en', 'modificado_por', 'modificado_en',
    ];

    public static function labelSemestre(string $semestre): string
    {
        return match ($semestre) {
            'S1' => 'I Semestre',
            'S2' => 'II Semestre',
            default => "Semestre {$semestre}",
        };
    }

    protected function casts(): array
    {
        return [
            'pct_docencia'       => 'decimal:2',
            'pct_investigacion'  => 'decimal:2',
            'pct_extension'      => 'decimal:2',
            'pct_administracion' => 'decimal:2',
            'pct_otras'          => 'decimal:2',
            'hrs_docencia'       => 'decimal:2',
            'hrs_investigacion'  => 'decimal:2',
            'hrs_extension'      => 'decimal:2',
            'hrs_administracion' => 'decimal:2',
            'hrs_otras'          => 'decimal:2',
            'confirmado_en'      => 'datetime',
            'modificado_en'      => 'datetime',
        ];
    }

    /**
     * Calcula pct_* a partir de horas declaradas.
     *
     * Método: normalización sobre el total declarado → los 4 pct_ suman 100%.
     * Áreas con 0 horas reciben 0%. El ajuste de redondeo se aplica al último
     * área con horas > 0 (el CHECK de suma=100 fue eliminado en migración 047).
     *
     * @param  array{docencia:float, investigacion:float, extension:float, administracion:float} $horas
     * @param  int   $decimales  Desde configuraciones_apa.decimales_pct (default: 2)
     * @return array{pct_docencia:float, pct_investigacion:float, pct_extension:float, pct_administracion:float}
     */
    public static function calcularPorcentajesDesdeHoras(array $horas, int $decimales = 2): array
    {
        $areas = ['docencia', 'investigacion', 'extension', 'administracion'];

        $totalHrs = array_sum(array_map(fn ($a) => (float) ($horas[$a] ?? 0), $areas));

        if ($totalHrs <= 0) {
            return array_combine(
                array_map(fn ($a) => "pct_{$a}", $areas),
                array_fill(0, count($areas), 0.0)
            );
        }

        $areasConHrs = array_values(array_filter($areas, fn ($a) => (float) ($horas[$a] ?? 0) > 0));

        $pcts    = [];
        $sumaAcum = 0.0;

        foreach ($areas as $area) {
            $hrs = (float) ($horas[$area] ?? 0);
            if ($hrs <= 0.0) {
                $pcts["pct_{$area}"] = 0.0;
            } else {
                $pct = round($hrs / $totalHrs * 100, $decimales);
                $pcts["pct_{$area}"] = $pct;
                $sumaAcum += $pct;
            }
        }

        // Ajuste en el último área con horas para garantizar pct_sum = 100 exacto.
        if (!empty($areasConHrs)) {
            $lastKey          = 'pct_' . end($areasConHrs);
            $pcts[$lastKey]   = round(100 - ($sumaAcum - $pcts[$lastKey]), $decimales);
        }

        return $pcts;
    }

    public function estaConfirmado(): bool
    {
        return $this->confirmado_en !== null;
    }

    public function sumaPorcentajes(): float
    {
        return (float) $this->pct_docencia
            + (float) $this->pct_investigacion
            + (float) $this->pct_extension
            + (float) $this->pct_administracion
            + (float) $this->pct_otras;
    }

    /** @return array<string, float> */
    public function toPesosArray(): array
    {
        return [
            'docencia'      => (float) $this->pct_docencia,
            'investigacion' => (float) $this->pct_investigacion,
            'vinculacion'   => (float) $this->pct_extension,
            'gestion'       => (float) $this->pct_administracion,
            'formacion'     => (float) $this->pct_otras,
        ];
    }

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class);
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }
}
