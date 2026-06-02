<?php

namespace App\Http\Controllers;

use App\Models\ComentarioVicerrectora;
use App\Models\Evaluacion;
use App\Models\Facultad;
use App\Models\Nomina;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class VicerrectoraController extends Controller
{
    public function dashboard(): Response
    {
        $periodo = Periodo::where('estado', 'activo')->latest()->first();

        $facultades = Facultad::orderBy('nombre')->get()->map(fn ($f) => [
            'id'     => $f->id,
            'nombre' => $f->nombre,
        ]);

        $academicos = collect();

        if ($periodo) {
            $academicos = Nomina::with(['academico.facultad', 'calificacionFinal.evaluacion.comentarioVicerrectora'])
                ->where('periodo_id', $periodo->id)
                ->whereNotNull('academico_id')
                ->get()
                ->map(fn (Nomina $n) => $this->serializarNomina($n))
                ->sortBy(['facultad', 'academico'])
                ->values();
        }

        return Inertia::render('Vicerrectora/Dashboard', [
            'periodo'    => $periodo?->only(['id', 'anio', 'nombre']),
            'facultades' => $facultades,
            'academicos' => $academicos,
        ]);
    }

    public function expediente(Nomina $nomina): Response
    {
        $nomina->load([
            'academico.facultad',
            'evidenciasNormales.categoria',
            'calificacionFinal.evaluacion.comentarioVicerrectora',
        ]);

        return Inertia::render('Vicerrectora/Expediente', [
            'nomina'   => $this->serializarNomina($nomina),
            'evidencias' => $nomina->evidenciasNormales->map(fn ($e) => [
                'id'             => $e->id,
                'categoria'      => $e->categoria->nombre,
                'nombre_archivo' => $e->nombre_archivo,
            ]),
        ]);
    }

    public function comentar(Request $request, Nomina $nomina)
    {
        $data = $request->validate([
            'comentario' => ['required', 'string', 'max:2000'],
        ]);

        $evaluacion = $nomina->calificacionFinal?->evaluacion;

        if (!$evaluacion) {
            return back()->with('error', 'Este académico aún no tiene evaluación registrada.');
        }

        ComentarioVicerrectora::updateOrCreate(
            ['evaluacion_id' => $evaluacion->id],
            ['comentario' => $data['comentario'], 'creado_por' => $request->user()->id]
        );

        return back()->with('success', 'Comentario guardado.');
    }

    private function serializarNomina(Nomina $n): array
    {
        $cf  = $n->calificacionFinal;
        $ev  = $cf?->evaluacion;
        $comentario = $ev?->comentarioVicerrectora;

        $vigenteHasta = $ev?->vigente_hasta;
        $vencida      = $vigenteHasta && Carbon::parse($vigenteHasta)->isPast();

        return [
            'id'           => $n->id,
            'academico'    => $n->academico->name,
            'rut'          => $n->academico->rut,
            'facultad'     => $n->academico->facultad?->nombre,
            'categoria'    => $n->academico->categoria_academica,
            'nota_final'   => $cf?->nota_final,
            'sin_calificacion'        => $ev?->sin_calificacion ?? false,
            'motivo_sin_calificacion' => $ev?->motivo_sin_calificacion,
            'vigente_hasta'  => $vigenteHasta?->format('d/m/Y'),
            'nota_vencida'   => $vencida,
            'comentario'     => $comentario?->comentario,
        ];
    }
}
