<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNominaRequest;
use App\Models\Facultad;
use App\Models\Nomina;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class NominaController extends Controller
{
    public function create(Periodo $periodo): Response
    {
        $facultades = Facultad::orderBy('nombre')->get(['id', 'nombre']);

        $academicos = User::activos()
            ->deRol('academico')
            ->whereNotNull('facultad_id')
            ->orderBy('name')
            ->get(['id', 'name', 'rut', 'facultad_id']);

        $yaEnNomina = Nomina::where('periodo_id', $periodo->id)
            ->pluck('user_id')
            ->all();

        return Inertia::render('Nomina/Create', [
            'periodo'    => $periodo->only(['id', 'anio', 'nombre', 'estado']),
            'facultades' => $facultades,
            'academicos' => $academicos,
            'yaEnNomina' => $yaEnNomina,
        ]);
    }

    public function store(StoreNominaRequest $request, Periodo $periodo)
    {
        $userIds = $request->validated()['user_ids'];

        $yaEnNomina = Nomina::where('periodo_id', $periodo->id)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->all();

        $nuevos = array_values(array_diff($userIds, $yaEnNomina));

        if (empty($nuevos)) {
            return back()->with('error', 'Todos los académicos seleccionados ya están en la nómina.');
        }

        $now     = now();
        $nominas = array_map(fn ($uid) => [
            'id'                   => (string) Str::uuid(),
            'periodo_id'           => $periodo->id,
            'user_id'              => $uid,
            'estado'               => 'pendiente',
            'con_licencia'         => false,
            'observacion_licencia' => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $nuevos);

        DB::table('nominas')->insert($nominas);

        $agregados = count($nominas);
        $omitidos  = count($userIds) - $agregados;
        $msg       = "{$agregados} académico(s) agregado(s) a la nómina.";
        if ($omitidos > 0) {
            $msg .= " {$omitidos} ya estaba(n) en la nómina y fue(ron) omitido(s).";
        }

        return redirect()
            ->route('analista.periodos.nominas.create', $periodo->id)
            ->with('success', $msg);
    }
}
