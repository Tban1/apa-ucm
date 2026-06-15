<?php

namespace App\Http\Controllers;

use App\Models\Periodo;
use App\Models\SemestreAcademico;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function configuracionSemestres(): Response
    {
        $periodo = Periodo::where('estado', 'activo')->with('semestres')->latest()->first();

        $semestres = null;
        if ($periodo) {
            $semestreS1 = $periodo->semestres->firstWhere('numero', 1);
            $semestreS2 = $periodo->semestres->firstWhere('numero', 2);

            $semestres = [
                's1' => $semestreS1 ? [
                    'id' => $semestreS1->id,
                    'fecha_cierre' => $semestreS1->fecha_cierre->format('Y-m-d'),
                ] : null,
                's2' => $semestreS2 ? [
                    'id' => $semestreS2->id,
                    'fecha_cierre' => $semestreS2->fecha_cierre->format('Y-m-d'),
                ] : null,
            ];
        }

        return Inertia::render('Admin/ConfiguracionSemestres', [
            'periodo' => $periodo?->only(['id', 'anio', 'nombre']),
            'semestres' => $semestres,
        ]);
    }

    public function storeSemestres(Request $request)
    {
        $periodo = Periodo::where('estado', 'activo')->latest()->first();

        if (!$periodo) {
            return back()->with('error', 'No hay período activo.');
        }

        $validated = $request->validate([
            'fecha_cierre_s1' => ['required', 'date'],
            'fecha_cierre_s2' => ['required', 'date', 'after:fecha_cierre_s1'],
        ]);

        // Crear o actualizar I Semestre
        SemestreAcademico::updateOrCreate(
            ['periodo_id' => $periodo->id, 'numero' => 1],
            ['fecha_cierre' => $validated['fecha_cierre_s1']]
        );

        // Crear o actualizar II Semestre
        SemestreAcademico::updateOrCreate(
            ['periodo_id' => $periodo->id, 'numero' => 2],
            ['fecha_cierre' => $validated['fecha_cierre_s2']]
        );

        return back()->with('success', 'Fechas de cierre de semestres actualizadas correctamente.');
    }
}
