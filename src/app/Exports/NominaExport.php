<?php

namespace App\Exports;

use App\Models\Nomina;
use App\Models\Periodo;
use App\Services\CalificacionCadService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NominaExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private Periodo $periodo,
        private ?string $facultadId = null,
    ) {}

    public function collection()
    {
        $query = Nomina::with(['academico.facultad', 'compromisoApa'])
            ->where('periodo_id', $this->periodo->id);

        if ($this->facultadId) {
            $query->where(function ($q) {
                $q->where('facultad_id', $this->facultadId)
                    ->orWhereHas('academico', fn ($a) => $a->where('facultad_id', $this->facultadId));
            });
        }

        return $query->orderBy('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'RUT', 'Nombre', 'Facultad', 'Categoría', 'Horas de contrato', 'Compromiso APA',
        ];
    }

    public function map($nomina): array
    {
        $a = $nomina->academico;
        $c = $nomina->compromisoApa;

        return [
            $a->rut,
            $a->name,
            $nomina->academico->facultad?->nombre ?? $a->facultad?->nombre,
            CalificacionCadService::labelCategoria($nomina->categoria ?? $a->categoria_academica),
            $nomina->horas_contrato ?? (($a->horas_contrato_isem ?? 0) + ($a->horas_contrato_iisem ?? 0)),
            $c && $c->estaConfirmado() ? 'Confirmado' : 'Pendiente',
        ];
    }
}
