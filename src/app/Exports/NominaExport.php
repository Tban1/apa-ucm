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
    private ?array $columnasAdicionales = null;

    public function __construct(
        private readonly ?Periodo $periodo,
        private readonly ?string  $facultadId = null,
        private readonly bool     $soloPlantilla = false,
        private readonly bool     $soloExcelentes = false,
    ) {}

    private function getColumnasAdicionales(): array
    {
        if ($this->columnasAdicionales !== null) {
            return $this->columnasAdicionales;
        }

        if ($this->soloPlantilla || !$this->periodo) {
            return $this->columnasAdicionales = [];
        }

        $this->columnasAdicionales = Nomina::where('periodo_id', $this->periodo->id)
            ->whereNotNull('datos_adicionales')
            ->get(['datos_adicionales'])
            ->flatMap(fn ($n) => array_keys($n->datos_adicionales ?? []))
            ->unique()
            ->values()
            ->all();

        return $this->columnasAdicionales;
    }

    public function collection()
    {
        if ($this->soloPlantilla || !$this->periodo) {
            return collect();
        }

        $extras = $this->getColumnasAdicionales();

        return Nomina::with(['academico.facultad', 'calificacionFinal'])
            ->where('periodo_id', $this->periodo->id)
            ->when($this->facultadId, fn ($q) =>
                $q->whereHas('academico', fn ($q2) =>
                    $q2->where('facultad_id', $this->facultadId)
                )
            )
            ->when($this->soloExcelentes, fn ($q) =>
                $q->whereHas('calificacionFinal', fn ($q2) =>
                    $q2->where('calificacion', 'excelente')
                )
            )
            ->orderBy('created_at')
            ->get()
            ->map(function (Nomina $n) use ($extras) {
                $fila = [
                    $n->numero_personal                                      ?? '',
                    $n->rut   ?? $n->academico?->rut                        ?? '',
                    $n->nombre ?? $n->academico?->name                      ?? '',
                    $n->adscripcion_academica                                ?? '',
                    $n->unidad_superior ?? $n->academico?->facultad?->nombre ?? '',
                    $n->unidad                                               ?? '',
                    $n->nombre_posicion                                      ?? '',
                    $n->tipo_trabajador                                      ?? '',
                    $n->fecha_inicio_contrato?->format('d/m/Y')             ?? '',
                    $n->horas_contrato                                       ?? '',
                    $n->categoria ?? $n->academico?->categoria_academica    ?? '',
                    $n->fecha_categorizacion?->format('d/m/Y')              ?? '',
                ];

                if ($this->soloExcelentes) {
                    $fila[] = $n->calificacionFinal?->nota_final
                        ? number_format((float) $n->calificacionFinal->nota_final, 2)
                        : '';
                    $fila[] = $n->calificacionFinal?->calificacionLabel() ?? '';
                } else {
                    $fila[] = ucfirst($n->estado);
                    $fila[] = $n->con_licencia ? 'Sí' : 'No';
                    $fila[] = $n->observacion_licencia ?? '';

                    foreach ($extras as $col) {
                        $fila[] = $n->datos_adicionales[$col] ?? '';
                    }
                }

                return $fila;
            });
    }

    public function headings(): array
    {
        $fijos = [
            'N° Personal',
            'Cédula de Identidad',
            'Nombre del Trabajador',
            'Adscripción Académica',
            'Unidad Superior',
            'Unidad',
            'Nombre de la Posición',
            'Tipo de Trabajador',
            'Fecha de Inicio de Contrato',
            'Horas de Contrato',
            'Categoría',
            'Fecha Categoría',
        ];

        if ($this->soloExcelentes) {
            return array_merge($fijos, ['Nota Final', 'Concepto']);
        }

        return array_merge($fijos, ['Estado', 'Con Licencia', 'Observación Licencia'], $this->getColumnasAdicionales());
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        $base = [
            'A' => 16, 'B' => 20, 'C' => 30, 'D' => 22,
            'E' => 28, 'F' => 22, 'G' => 26, 'H' => 18,
            'I' => 22, 'J' => 14, 'K' => 16, 'L' => 20,
            'M' => 14, 'N' => 14, 'O' => 30,
        ];

        $extras = $this->getColumnasAdicionales();
        $col    = ord('P');
        foreach ($extras as $_) {
            $base[chr($col)] = 22;
            $col++;
        }

        return $base;
    }
}
