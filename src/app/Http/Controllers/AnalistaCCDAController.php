<?php

namespace App\Http\Controllers;

use App\Models\Acta;
use App\Models\CalificacionFinal;
use App\Models\Facultad;
use App\Models\Nomina;
use App\Models\Periodo;
use App\Models\PlazoFacultad;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class AnalistaCCDAController extends Controller
{
    public function estadoProceso(): Response
    {
        $periodos = Periodo::orderByDesc('anio')->get(['id', 'anio', 'nombre', 'estado']);
        $periodo  = $periodos->firstWhere('estado', 'activo') ?? $periodos->first();

        $facultades = collect();

        if ($periodo) {
            $facultades = Facultad::orderBy('nombre')
                ->get()
                ->map(function ($f) use ($periodo) {
                    $nominas = Nomina::where('periodo_id', $periodo->id)
                        ->whereHas('academico', fn ($q) => $q->where('facultad_id', $f->id))
                        ->get(['estado', 'con_licencia']);

                    if ($nominas->isEmpty()) {
                        return null;
                    }

                    $plazo = PlazoFacultad::where('periodo_id', $periodo->id)
                        ->where('facultad_id', $f->id)
                        ->first();

                    $actaCierre = Acta::where('periodo_id', $periodo->id)
                        ->where('facultad_id', $f->id)
                        ->where('tipo', 'cierre_proceso')
                        ->first();

                    $estados = $nominas->countBy('estado')->toArray();
                    $total   = $nominas->count();

                    return [
                        'id'           => $f->id,
                        'nombre'       => $f->nombre,
                        'total'        => $total,
                        'con_licencia' => $nominas->where('con_licencia', true)->count(),
                        'estados'      => $estados,
                        'evaluados'    => ($estados['evaluado'] ?? 0) + ($estados['cerrado'] ?? 0),
                        'recepcion_cerrada' => $plazo?->estaCerradoFormalmente() ?? false,
                        'proceso_cerrado'   => $actaCierre !== null,
                        'acta_id'      => $actaCierre?->id,
                    ];
                })
                ->filter()
                ->values();
        }

        return Inertia::render('AnalistaCCDA/EstadoProceso', [
            'periodo'   => $periodo?->only(['id', 'anio', 'nombre', 'estado']),
            'periodos'  => $periodos->map->only(['id', 'anio', 'nombre', 'estado']),
            'facultades' => $facultades,
        ]);
    }

    public function reporteCalificaciones(): View
    {
        $periodo = Periodo::where('estado', 'activo')->latest()->first()
            ?? Periodo::latest()->first();

        if (!$periodo) {
            abort(404, 'No hay períodos registrados.');
        }

        $facultades = Facultad::orderBy('nombre')->get()->map(function ($f) use ($periodo) {
            $nominas = Nomina::with(['academico.departamento', 'calificacionFinal'])
                ->where('periodo_id', $periodo->id)
                ->whereHas('academico', fn ($q) => $q->where('facultad_id', $f->id))
                ->orderBy('created_at')
                ->get();

            if ($nominas->isEmpty()) {
                return null;
            }

            $academicos = $nominas->map(fn ($n) => [
                'nombre'       => $n->academico->name,
                'rut'          => $n->academico->rut,
                'departamento' => $n->academico->departamento?->nombre,
                'calificacion' => $n->calificacionFinal?->calificacion,
                'puntaje'      => $n->calificacionFinal?->puntaje_total,
                'es_apelacion' => $n->calificacionFinal?->es_apelacion ?? false,
            ]);

            $califs = $academicos->pluck('calificacion')->filter()->countBy();

            return [
                'nombre'     => $f->nombre,
                'academicos' => $academicos,
                'resumen'    => [
                    'total'       => $academicos->count(),
                    'con_calif'   => $academicos->filter(fn ($a) => $a['calificacion'])->count(),
                    'muy_bueno'   => $califs['muy_bueno']  ?? 0,
                    'bueno'       => $califs['bueno']      ?? 0,
                    'aceptable'   => $califs['aceptable']  ?? 0,
                    'deficiente'  => $califs['deficiente'] ?? 0,
                ],
            ];
        })->filter()->values();

        return view('analista.reporte_calificaciones', compact('periodo', 'facultades'));
    }

    public function incumplimientos(): View
    {
        $periodo = Periodo::where('estado', 'activo')->latest()->first()
            ?? Periodo::latest()->first();

        if (!$periodo) {
            abort(404, 'No hay períodos registrados.');
        }

        $facultades = Facultad::orderBy('nombre')->get()->map(function ($f) use ($periodo) {
            $plazo = PlazoFacultad::where('periodo_id', $periodo->id)
                ->where('facultad_id', $f->id)
                ->whereNotNull('cerrado_en')
                ->first();

            if (!$plazo) {
                return null;
            }

            $nominas = Nomina::with(['academico.departamento'])
                ->where('periodo_id', $periodo->id)
                ->whereHas('academico', fn ($q) => $q->where('facultad_id', $f->id))
                ->where('con_licencia', false)
                ->whereNotIn('estado', ['evaluado', 'cerrado'])
                ->orderBy('created_at')
                ->get();

            if ($nominas->isEmpty()) {
                return null;
            }

            return [
                'nombre'     => $f->nombre,
                'academicos' => $nominas->map(fn ($n) => [
                    'nombre'       => $n->academico->name,
                    'rut'          => $n->academico->rut,
                    'departamento' => $n->academico->departamento?->nombre,
                    'estado'       => $n->estado,
                    'evidencias'   => $n->evidenciasNormales()->count(),
                ]),
            ];
        })->filter()->values();

        return view('analista.incumplimientos', compact('periodo', 'facultades'));
    }
}
