<?php

namespace App\Http\Controllers;

use App\Models\CompromisoApa;
use App\Models\Nomina;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CompromisoApaController extends Controller
{
    public function showDeclaracion(string $semestre = 'S1'): Response
    {
        // 1. Validar que semestre es 'S1' o 'S2'
        if (!in_array($semestre, ['S1', 'S2'])) {
            abort(404);
        }

        $user    = auth()->user();
        $periodo = Periodo::where('estado', 'activo')->with('semestres')->latest()->first();

        if (!$periodo) {
            return Inertia::render('Academico/DeclaracionApa', [
                'periodo'         => null,
                'nomina'          => null,
                'semestre'        => $semestre,
                'semestreLabel'   => CompromisoApa::labelSemestre($semestre),
                'yaDeclarado'     => false,
                'fechaCierre'     => null,
                'datos'           => null,
            ]);
        }

        $nomina = Nomina::with(['academico', 'compromisos'])
            ->where('periodo_id', $periodo->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$nomina) {
            abort(403, 'No está incluido en la nómina del período activo.');
        }

        if (!$nomina->cargaEvidenciasHabilitada()) {
            return redirect()->route('academico.dashboard')
                ->with('error', 'El plazo de carga de evidencias no está vigente.');
        }

        // 2. Obtener datos del semestre
        $semestres = $periodo->semestres;
        $semestreData = $semestres->firstWhere('numero', $semestre === 'S1' ? 1 : 2);

        // 3. Verificar si el semestre está disponible para declarar
        if ($semestre === 'S2') {
            $cierreS1 = $semestres->firstWhere('numero', 1)?->fecha_cierre;
            if (!$cierreS1 || !today()->isAfter($cierreS1)) {
                return redirect()->route('academico.dashboard')
                    ->with('error', 'El II Semestre estará disponible cuando cierre el I Semestre.');
            }
        }

        // 4. Verificar si ya declaró este semestre
        $compromisoExistente = CompromisoApa::where('nomina_id', $nomina->id)
            ->where('semestre', $semestre)
            ->first();

        return Inertia::render('Academico/DeclaracionApa', [
            'periodo'       => $periodo->only(['id', 'anio', 'nombre']),
            'nomina'        => ['id' => $nomina->id],
            'semestre'      => $semestre,
            'semestreLabel' => CompromisoApa::labelSemestre($semestre),
            'yaDeclarado'   => $compromisoExistente && $compromisoExistente->estaConfirmado(),
            'fechaCierre'   => $semestreData?->fecha_cierre?->format('d/m/Y'),
            'datos'         => $compromisoExistente ? [
                'pct_docencia'       => (float) $compromisoExistente->pct_docencia,
                'pct_investigacion'  => (float) $compromisoExistente->pct_investigacion,
                'pct_extension'      => (float) $compromisoExistente->pct_extension,
                'pct_administracion' => (float) $compromisoExistente->pct_administracion,
            ] : null,
        ]);
    }

    public function storeDeclaracion(Request $request)
    {
        $user    = auth()->user();
        $periodo = Periodo::where('estado', 'activo')->with('semestres')->latest()->first();

        if (!$periodo) {
            return back()->with('error', 'No hay período activo.');
        }

        $nomina = Nomina::with(['academico', 'compromisos'])
            ->where('periodo_id', $periodo->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (!$nomina->cargaEvidenciasHabilitada()) {
            return redirect()->route('academico.dashboard')
                ->with('error', 'El plazo de carga de evidencias no está vigente.');
        }

        // 1. Validar semestre y porcentajes
        $validated = $request->validate([
            'semestre'           => ['required', 'in:S1,S2'],
            'pct_docencia'       => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_investigacion'  => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_extension'      => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_administracion' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $semestre = $validated['semestre'];

        // Verificar que este semestre no está ya confirmado
        $existente = $nomina->compromisos->firstWhere('semestre', $semestre);
        if ($existente?->estaConfirmado()) {
            return back()->with('error', 'Este semestre ya fue confirmado.');
        }

        // 2. Validar que suma sea 100
        $suma = (float) $validated['pct_docencia'] 
              + (float) $validated['pct_investigacion']
              + (float) $validated['pct_extension'] 
              + (float) $validated['pct_administracion'];

        if (abs($suma - 100) > 0.01) {
            throw ValidationException::withMessages([
                'pct_docencia' => "Los porcentajes deben sumar exactamente 100% (actual: {$suma}%).",
            ]);
        }

        // 3. Guardar compromiso
        CompromisoApa::updateOrCreate(
            ['nomina_id' => $nomina->id, 'semestre' => $semestre],
            [
                'periodo_id'         => $periodo->id,
                'pct_docencia'       => round((float) $validated['pct_docencia'], 2),
                'pct_investigacion'  => round((float) $validated['pct_investigacion'], 2),
                'pct_extension'      => round((float) $validated['pct_extension'], 2),
                'pct_administracion' => round((float) $validated['pct_administracion'], 2),
                'pct_otras'          => 0,
                'fuente'             => 'manual',
                'confirmado_en'      => now(),
                'modificado_por'     => null,
                'modificado_en'      => null,
            ]
        );

        // 4. Redirigir según el caso
        if ($semestre === 'S1') {
            return redirect()->route('academico.dashboard')
                ->with('success', 'I Semestre confirmado. Podrás declarar el II Semestre cuando cierre el primero.');
        }

        return redirect()->route('academico.evidencias')
            ->with('success', 'II Semestre confirmado. Ya puedes cargar evidencias.');
    }

}
