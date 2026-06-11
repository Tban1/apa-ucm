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
    public function showDeclaracion(): Response
    {
        $user    = auth()->user();
        $periodo = Periodo::where('estado', 'activo')->latest()->first();

        if (!$periodo) {
            return Inertia::render('Academico/DeclaracionApa', [
                'periodo'           => null,
                'nomina'            => null,
                'semestres'         => [],
                'semestre_activo'   => null,
                'semestre_total'    => 0,
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

        $categoria    = $nomina->categoriaEfectiva();
        $total        = CompromisoApa::semestresParaCategoria($categoria);
        $existentes   = $nomina->compromisos->keyBy('semestre');

        $semestres = collect(range(1, $total))->map(function (int $n) use ($existentes) {
            $c = $existentes->get((string) $n);
            return [
                'numero'     => $n,
                'label'      => CompromisoApa::labelSemestre($n),
                'confirmado' => $c?->estaConfirmado() ?? false,
                'datos'      => $c && !$c->estaConfirmado() ? [
                    'pct_docencia'       => (float) $c->pct_docencia,
                    'pct_investigacion'  => (float) $c->pct_investigacion,
                    'pct_extension'      => (float) $c->pct_extension,
                    'pct_administracion' => (float) $c->pct_administracion,
                    'pct_otras'          => (float) $c->pct_otras,
                ] : null,
            ];
        })->values()->all();

        // Primer semestre sin confirmar
        $semestreActivo = collect($semestres)->first(fn ($s) => !$s['confirmado'])['numero'] ?? null;

        if ($semestreActivo === null) {
            // Todos confirmados — redirigir a evidencias
            return redirect()->route('academico.evidencias');
        }

        return Inertia::render('Academico/DeclaracionApa', [
            'periodo'         => $periodo->only(['id', 'anio', 'nombre']),
            'nomina'          => ['id' => $nomina->id],
            'semestres'       => $semestres,
            'semestre_activo' => $semestreActivo,
            'semestre_total'  => $total,
        ]);
    }

    public function storeDeclaracion(Request $request)
    {
        $user    = auth()->user();
        $periodo = Periodo::where('estado', 'activo')->latest()->first();

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

        $categoria = $nomina->categoriaEfectiva();
        $total     = CompromisoApa::semestresParaCategoria($categoria);

        $validated = $request->validate([
            'semestre' => ['required', 'integer', 'min:1', "max:{$total}"],
        ]);

        $semestre = (string) $validated['semestre'];

        // Verificar que este semestre no está ya confirmado
        $existente = $nomina->compromisos->firstWhere('semestre', $semestre);
        if ($existente?->estaConfirmado()) {
            return back()->with('error', 'Este semestre ya fue confirmado.');
        }

        $data = $this->validarPorcentajes($request);

        CompromisoApa::updateOrCreate(
            ['nomina_id' => $nomina->id, 'semestre' => $semestre],
            array_merge($data, [
                'periodo_id'     => $periodo->id,
                'fuente'         => 'manual',
                'confirmado_en'  => now(),
                'modificado_por' => null,
                'modificado_en'  => null,
            ])
        );

        // Si quedan semestres por confirmar → volver a la declaración
        $confirmados = CompromisoApa::where('nomina_id', $nomina->id)
            ->whereNotNull('confirmado_en')
            ->count();

        if ($confirmados < $total) {
            return redirect()->route('academico.declaracion-apa')
                ->with('success', CompromisoApa::labelSemestre($semestre).' confirmado. Por favor completa el siguiente semestre.');
        }

        return redirect()->route('academico.evidencias')
            ->with('success', 'Distribución APA confirmada para todos los semestres. Ya puede cargar evidencias.');
    }

    /** @return array<string, float> */
    private function validarPorcentajes(Request $request): array
    {
        $data = $request->validate([
            'pct_docencia'       => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_investigacion'  => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_extension'      => ['required', 'numeric', 'min:0', 'max:100'],
            'pct_administracion' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // pct_otras ya no forma parte de la declaración APA
        $data['pct_otras'] = 0;

        $suma = (float) $data['pct_docencia'] + (float) $data['pct_investigacion']
              + (float) $data['pct_extension'] + (float) $data['pct_administracion'];

        if (abs($suma - 100) > 0.01) {
            throw ValidationException::withMessages([
                'pct_docencia' => "Los porcentajes deben sumar exactamente 100% (actual: {$suma}%).",
            ]);
        }

        return array_map(fn ($v) => round((float) $v, 2), $data);
    }
}
