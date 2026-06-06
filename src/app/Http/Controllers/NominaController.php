<?php

namespace App\Http\Controllers;

use App\Exports\NominaExport;
use App\Exports\NominaPlantillaExport;
use App\Http\Requests\StoreNominaRequest;
use App\Models\Facultad;
use App\Models\Nomina;
use App\Models\Periodo;
use App\Models\User;
use App\Services\NominaExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class NominaController extends Controller
{
    public function create(Periodo $periodo): Response
    {
        return $this->excel($periodo);
    }

    public function excel(Periodo $periodo): Response
    {
        $facultades = Facultad::orderBy('nombre')->get(['id', 'nombre']);

        $nominas = Nomina::with(['academico.facultad', 'facultad', 'compromisoApa'])
            ->where('periodo_id', $periodo->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (Nomina $n) => $this->serializarFila($n));

        return Inertia::render('Nomina/Excel', [
            'periodo'    => $periodo->only(['id', 'anio', 'nombre', 'estado']),
            'facultades' => $facultades,
            'filas'      => $nominas->values(),
        ]);
    }

    public function store(StoreNominaRequest $request, Periodo $periodo)
    {
        return $this->storeGrid($request, $periodo);
    }

    public function storeGrid(Request $request, Periodo $periodo)
    {
        $data = $request->validate([
            'filas'                          => ['required', 'array', 'min:1'],
            'filas.*.rut'                    => ['required', 'string'],
            'filas.*.nombre'                 => ['required', 'string'],
            'filas.*.facultad_id'            => ['nullable', 'uuid'],
            'filas.*.categoria'              => ['required', 'in:auxiliar,adjunto,titular'],
            'filas.*.horas_contrato'         => ['nullable', 'integer', 'min:0'],
            'filas.*.user_id'                => ['nullable', 'uuid'],
            'filas.*.datos_adicionales'      => ['nullable', 'array'],
        ]);

        $service = new NominaExcelService;
        $validas = $service->validarFilas($data['filas']);

        $invalidas = collect($validas)->filter(fn ($f) => !$f['valido']);
        if ($invalidas->isNotEmpty()) {
            return back()->withErrors([
                'filas' => 'Hay filas con errores de validación. Revise RUT y categoría.',
            ])->with('filasConError', $validas);
        }

        $guardadas = 0;

        DB::transaction(function () use ($validas, $periodo, &$guardadas) {
            foreach ($validas as $fila) {
                $user = User::find($fila['user_id']);
                if (!$user) {
                    continue;
                }

                Nomina::updateOrCreate(
                    ['periodo_id' => $periodo->id, 'user_id' => $user->id],
                    [
                        'facultad_id'       => $fila['facultad_id'],
                        'categoria'         => $fila['categoria'],
                        'horas_contrato'    => $fila['horas_contrato'] ?? null,
                        'datos_adicionales' => $fila['datos_adicionales'] ?? null,
                        'estado'            => 'pendiente',
                    ]
                );

                $guardadas++;
            }
        });

        return redirect()
            ->route('analista.periodos.nominas.create', $periodo->id)
            ->with('success', "{$guardadas} registro(s) guardado(s) en la nómina.");
    }

    public function importPreview(Request $request, Periodo $periodo)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $rows    = Excel::toArray([], $request->file('archivo'))[0] ?? [];
        $service = new NominaExcelService;
        $filas   = $service->validarFilas($service->parsearFilas($rows));

        return back()->with([
            'importPreview' => $filas,
            'success'       => count($filas).' fila(s) importada(s) para revisión.',
        ]);
    }

    public function downloadPlantilla(): BinaryFileResponse
    {
        return Excel::download(new NominaPlantillaExport, 'plantilla_nomina.xlsx');
    }

    public function exportExcel(Request $request, Periodo $periodo): BinaryFileResponse
    {
        $facultadId = $request->query('facultad_id');
        $facultad   = $facultadId ? Facultad::find($facultadId) : null;
        $anio       = $periodo->anio;
        $slug       = $facultad
            ? Str::slug($facultad->nombre)
            : 'todas';

        return Excel::download(
            new NominaExport($periodo, $facultadId),
            "nomina_{$slug}_{$anio}.xlsx"
        );
    }

    public function toggleLicencia(Request $request, Nomina $nomina)
    {
        $data = $request->validate([
            'con_licencia'         => ['required', 'boolean'],
            'observacion_licencia' => ['nullable', 'string', 'max:500', 'required_if:con_licencia,true'],
        ], [
            'observacion_licencia.required_if' => 'El motivo del caso especial es obligatorio.',
        ]);

        $nomina->update([
            'con_licencia'         => $data['con_licencia'],
            'observacion_licencia' => $data['con_licencia'] ? $data['observacion_licencia'] : null,
        ]);

        $msg = $data['con_licencia']
            ? 'Caso especial registrado correctamente.'
            : 'Caso especial removido.';

        return back()->with('success', $msg);
    }

    private function serializarFila(Nomina $n): array
    {
        $a = $n->academico;

        $c = $n->compromisoApa;

        return [
            'id'                 => $n->id,
            'user_id'            => $n->user_id,
            'rut'                => $a->rut,
            'nombre'             => $a->name,
            'facultad_id'        => $n->facultad_id ?? $a->facultad_id,
            'facultad_nombre'    => $n->facultad?->nombre ?? $a->facultad?->nombre,
            'categoria'          => $n->categoria ?? $a->categoria_academica ?? 'adjunto',
            'horas_contrato'     => $n->horas_contrato ?? (($a->horas_contrato_isem ?? 0) + ($a->horas_contrato_iisem ?? 0)),
            'datos_adicionales'  => $n->datos_adicionales,
            'estado'             => $n->estado,
            'compromiso_apa'     => $c ? [
                'confirmado' => $c->estaConfirmado(),
            ] : null,
        ];
    }
}
