<?php

namespace App\Exports;

use App\Models\Nomina;
use App\Models\Periodo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NominaExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(
        private readonly Periodo $periodo,
        private readonly ?string $facultadId = null,
    ) {}

    public function collection()
    {
        return Nomina::with(['academico.facultad'])
            ->where('periodo_id', $this->periodo->id)
            ->when($this->facultadId, fn ($q) =>
                $q->whereHas('academico', fn ($q2) =>
                    $q2->where('facultad_id', $this->facultadId)
                )
            )
            ->orderBy('created_at')
            ->get()
            ->map(fn (Nomina $n) => [
                $n->academico->rut                ?? '—',
                $n->academico->name               ?? '—',
                $n->academico->email              ?? '—',
                $n->academico->facultad?->nombre  ?? '—',
                $n->academico->facultad?->codigo  ?? '—',
                ucfirst($n->academico->categoria_academica ?? '—'),
                $n->academico->horas_contrato_isem  ?? '—',
                $n->academico->horas_contrato_iisem ?? '—',
                ucfirst($n->estado),
                $n->con_licencia ? 'Sí' : 'No',
                $n->observacion_licencia ?? '—',
            ]);
    }

    public function headings(): array
    {
        return [
            'RUT',
            'Nombre',
            'Email',
            'Facultad',
            'Código Facultad',
            'Categoría Académica',
            'Horas Contrato I Sem',
            'Horas Contrato II Sem',
            'Estado',
            'Con Licencia',
            'Observación Licencia',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16, 'B' => 30, 'C' => 28, 'D' => 30,
            'E' => 14, 'F' => 20, 'G' => 20, 'H' => 20,
            'I' => 14, 'J' => 14, 'K' => 30,
        ];
    }
}
