<?php

namespace App\Http\Controllers;

use App\Exports\NominaExport;
use App\Http\Requests\StoreNominaRequest;
use App\Models\Facultad;
use App\Models\Nomina;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NominaController extends Controller
{
    public function create(Periodo $periodo): Response
    {
        $facultades = Facultad::orderBy('nombre')->get(['id', 'nombre', 'codigo']);

        $academicos = User::activos()
            ->deRol('academico')
            ->whereNotNull('facultad_id')
            ->orderBy('name')
            ->get(['id', 'name', 'rut', 'facultad_id']);

        $nominasEnPeriodo = Nomina::where('periodo_id', $periodo->id)
            ->get(['id', 'user_id', 'estado', 'con_licencia', 'observacion_licencia', 'updated_at']);

        return Inertia::render('Nomina/Create', [
            'periodo'          => $periodo->only(['id', 'anio', 'nombre', 'estado']),
            'facultades'       => $facultades,
            'academicos'       => $academicos,
            'nominasEnPeriodo' => $nominasEnPeriodo,
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

        $now = now();
        DB::table('nominas')->insert(array_map(fn ($uid) => [
            'id'                   => (string) Str::uuid(),
            'periodo_id'           => $periodo->id,
            'user_id'              => $uid,
            'estado'               => 'pendiente',
            'con_licencia'         => false,
            'observacion_licencia' => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ], $nuevos));

        $agregados = count($nuevos);
        $omitidos  = count($userIds) - $agregados;
        $msg = "{$agregados} académico(s) agregado(s) a la nómina.";
        if ($omitidos > 0) {
            $msg .= " {$omitidos} ya estaba(n) en la nómina y fue(ron) omitido(s).";
        }

        return redirect()
            ->route('analista.periodos.nominas.create', $periodo->id)
            ->with('success', $msg);
    }

    // ── Excel preview: lee encabezados + primeras filas ───────────────────────

    public function previewExcel(Request $request, Periodo $periodo)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'archivo.mimes' => 'Solo se aceptan archivos .xlsx, .xls o .csv.',
            'archivo.max'   => 'El archivo no puede superar los 5 MB.',
        ]);

        $path = $request->file('archivo')->store('tmp_nominas');
        $fullPath = storage_path('app/' . $path);

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = [];

            foreach ($sheet->getRowIterator(1, 6) as $row) {
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cells[] = (string) $cell->getValue();
                }
                // Trim trailing empty cells
                while (count($cells) > 0 && $cells[array_key_last($cells)] === '') {
                    array_pop($cells);
                }
                if (!empty(array_filter($cells))) {
                    $rows[] = $cells;
                }
            }
        } catch (\Throwable $e) {
            unlink($fullPath);
            return back()->withErrors(['archivo' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        if (empty($rows)) {
            unlink($fullPath);
            return back()->withErrors(['archivo' => 'El archivo está vacío.']);
        }

        // Guarda la ruta en sesión para el segundo paso
        session(['nomina_excel_path' => $path, 'nomina_excel_periodo' => $periodo->id]);

        return back()->with([
            'excel_preview' => [
                'columnas'     => $rows[0],
                'preview_rows' => array_slice($rows, 1, 4),
                'path'         => $path,
            ],
        ]);
    }

    // ── Importación: aplica el mapeo del usuario ──────────────────────────────

    public function importarExcel(Request $request, Periodo $periodo)
    {
        $data = $request->validate([
            'path'       => ['required', 'string'],
            'col_rut'    => ['required', 'integer', 'min:0'],
            'col_nombre' => ['required', 'integer', 'min:0'],
            'col_facultad'   => ['nullable', 'integer', 'min:0'],
            'col_categoria'  => ['nullable', 'integer', 'min:0'],
            'col_horas_isem' => ['nullable', 'integer', 'min:0'],
            'col_horas_iisem'=> ['nullable', 'integer', 'min:0'],
            'tiene_encabezado' => ['boolean'],
        ]);

        $fullPath = storage_path('app/' . $data['path']);

        if (!file_exists($fullPath)) {
            return back()->withErrors(['path' => 'El archivo ya no existe. Vuelve a subirlo.']);
        }

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $allRows     = [];
            foreach ($sheet->getRowIterator() as $row) {
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cells[] = (string) $cell->getValue();
                }
                $allRows[] = $cells;
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['path' => 'Error al leer el archivo: ' . $e->getMessage()]);
        }

        if ($data['tiene_encabezado']) {
            array_shift($allRows);
        }

        $facultades = Facultad::all()->keyBy(fn ($f) => mb_strtolower(trim($f->nombre)))
            ->merge(Facultad::all()->keyBy(fn ($f) => mb_strtolower(trim($f->codigo ?? ''))));

        $categoriasValidas = ['auxiliar', 'adjunto', 'titular', 'jerarquizado'];

        $creados = $omitidos = $errores = 0;
        $detalleErrores = [];

        foreach ($allRows as $i => $cells) {
            $fila = $i + ($data['tiene_encabezado'] ? 2 : 1);

            $rut    = trim($cells[$data['col_rut']]    ?? '');
            $nombre = trim($cells[$data['col_nombre']] ?? '');

            if (!$rut || !$nombre) {
                $errores++;
                $detalleErrores[] = "Fila {$fila}: RUT o Nombre vacío.";
                continue;
            }

            // Facultad
            $facultad = null;
            if (isset($data['col_facultad'])) {
                $val = mb_strtolower(trim($cells[$data['col_facultad']] ?? ''));
                $facultad = $facultades->get($val);
            }

            // Categoría
            $categoria = null;
            if (isset($data['col_categoria'])) {
                $cat = mb_strtolower(trim($cells[$data['col_categoria']] ?? ''));
                $categoria = in_array($cat, $categoriasValidas) ? $cat : null;
            }

            $horasIsem  = isset($data['col_horas_isem'])  ? (int) ($cells[$data['col_horas_isem']]  ?? 0) : null;
            $horasIIsem = isset($data['col_horas_iisem']) ? (int) ($cells[$data['col_horas_iisem']] ?? 0) : null;

            // Crear o actualizar usuario
            $user = User::where('rut', $rut)->first();

            if (!$user) {
                $emailBase = Str::slug($nombre, '.') . '@ucm.cl';
                $email     = User::where('email', $emailBase)->exists()
                    ? Str::slug($nombre, '.') . '.' . Str::random(4) . '@ucm.cl'
                    : $emailBase;

                $user = User::create([
                    'name'                 => $nombre,
                    'rut'                  => $rut,
                    'email'                => $email,
                    'password'             => Hash::make(Str::random(16)),
                    'role'                 => 'academico',
                    'facultad_id'          => $facultad?->id,
                    'categoria_academica'  => $categoria,
                    'horas_contrato_isem'  => $horasIsem,
                    'horas_contrato_iisem' => $horasIIsem,
                ]);
            } else {
                $updates = array_filter([
                    'facultad_id'          => $facultad?->id,
                    'categoria_academica'  => $categoria,
                    'horas_contrato_isem'  => $horasIsem,
                    'horas_contrato_iisem' => $horasIIsem,
                ], fn ($v) => $v !== null);

                if ($updates) {
                    $user->update($updates);
                }
            }

            // Agregar a nómina si no está
            $yaEsta = Nomina::where('periodo_id', $periodo->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($yaEsta) {
                $omitidos++;
                continue;
            }

            Nomina::create([
                'periodo_id'   => $periodo->id,
                'user_id'      => $user->id,
                'estado'       => 'pendiente',
                'con_licencia' => false,
            ]);

            $creados++;
        }

        @unlink($fullPath);

        $msg = "{$creados} académico(s) importado(s).";
        if ($omitidos)      { $msg .= " {$omitidos} ya estaban en la nómina."; }
        if ($errores)       { $msg .= " {$errores} fila(s) con errores omitidas."; }

        return redirect()
            ->route('analista.periodos.nominas.create', $periodo->id)
            ->with('success', $msg)
            ->with('import_errores', $detalleErrores);
    }

    // ── Agregar académico individual ──────────────────────────────────────────

    public function agregarIndividual(Request $request, Periodo $periodo)
    {
        $data = $request->validate([
            'rut'         => ['required', 'string', 'max:20'],
            'nombre'      => ['required', 'string', 'max:200'],
            'facultad_id' => ['required', 'uuid', 'exists:facultades,id'],
            'categoria'   => ['required', 'in:auxiliar,adjunto,titular,jerarquizado'],
        ], [
            'facultad_id.required' => 'La facultad es obligatoria.',
            'categoria.in'         => 'Categoría no válida.',
        ]);

        $user = User::where('rut', $data['rut'])->first();

        if (!$user) {
            $emailBase = Str::slug($data['nombre'], '.') . '@ucm.cl';
            $email     = User::where('email', $emailBase)->exists()
                ? Str::slug($data['nombre'], '.') . '.' . Str::random(4) . '@ucm.cl'
                : $emailBase;

            $user = User::create([
                'name'                => $data['nombre'],
                'rut'                 => $data['rut'],
                'email'               => $email,
                'password'            => Hash::make(Str::random(16)),
                'role'                => 'academico',
                'facultad_id'         => $data['facultad_id'],
                'categoria_academica' => $data['categoria'],
            ]);
        }

        $yaEsta = Nomina::where('periodo_id', $periodo->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($yaEsta) {
            return back()->with('error', "{$user->name} ya está en la nómina de este período.");
        }

        Nomina::create([
            'periodo_id'   => $periodo->id,
            'user_id'      => $user->id,
            'estado'       => 'pendiente',
            'con_licencia' => false,
        ]);

        return redirect()
            ->route('analista.periodos.nominas.create', $periodo->id)
            ->with('success', "{$user->name} agregado a la nómina.");
    }

    // ── Exportar nómina a Excel ───────────────────────────────────────────────

    public function exportar(Request $request, Periodo $periodo)
    {
        $facultadId = $request->query('facultad_id');

        $codigoFacultad = $facultadId
            ? (Facultad::find($facultadId)?->codigo ?? 'TODAS')
            : 'TODAS';

        $filename = 'nomina_' . $codigoFacultad . '_' . $periodo->anio . '.xlsx';

        return Excel::download(
            new NominaExport($periodo, $facultadId ?: null),
            $filename
        );
    }

    // ── Caso especial (licencia) ──────────────────────────────────────────────

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

        return back()->with('success', $data['con_licencia']
            ? 'Caso especial registrado correctamente.'
            : 'Caso especial removido.');
    }
}
