<?php

namespace Database\Seeders;

use App\Models\CategoriaApa;
use App\Models\Cronograma;
use App\Models\Evidencia;
use App\Models\Facultad;
use App\Models\Nomina;
use App\Models\Periodo;
use App\Models\PlazoFacultad;
use App\Models\Solicitud;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Carga datos realistas para la demo de Sprints 1-3.
 *
 * Crea (idempotente):
 *  - 1 período activo con cronograma completo (6 etapas secuenciales).
 *  - 1 plazo FCI vigente (no cerrado).
 *  - 5 académicos extra en la facultad FCI (sumados al `academico@ucm.cl` existente).
 *  - Nóminas para los 6 académicos: 4 pendientes, 1 con licencia, 1 en carga con
 *    2 evidencias PDF dummy (Docencia + Investigación) listas para mostrar.
 *
 * Diseñado para correrse después de `UsuariosPruebaSeeder` en entorno local.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $analista   = User::where('email', 'analista@ucm.cl')->first();
        $secretario = User::where('email', 'secretario@ucm.cl')->first();
        $academico  = User::where('email', 'academico@ucm.cl')->first();
        $fci        = Facultad::where('codigo', 'FCI')->first();

        if (!$analista || !$secretario || !$academico || !$fci) {
            $this->command?->warn(
                'DemoSeeder: faltan usuarios base (analista/secretario/academico) o la facultad FCI. '
                . 'Ejecute FacultadesSeeder + UsuariosPruebaSeeder antes.'
            );
            return;
        }

        $periodo = $this->crearPeriodoActivo($analista);
        $this->crearCronograma($periodo);
        $this->crearPlazoFCI($periodo, $fci, $secretario);

        $academicos = $this->crearAcademicosFCI($academico, $fci);
        $this->crearNominas($periodo, $academicos, $analista, $secretario);
        $this->crearEvidenciasDemo($periodo, $academico);
        $this->prepararDemoCCA($periodo, $academicos);
    }

    private function crearPeriodoActivo(User $analista): Periodo
    {
        $hoy  = Carbon::today();
        $anio = $hoy->year;

        return Periodo::firstOrCreate(
            ['nombre' => "{$anio}-1 - Calificación APA " . ($anio - 1)],
            [
                'anio'         => $anio,
                'estado'       => 'activo',
                'fecha_inicio' => $hoy->toDateString(),
                'fecha_cierre' => $hoy->copy()->addDays(150)->toDateString(),
                'creado_por'   => $analista->id,
            ]
        );
    }

    private function crearCronograma(Periodo $periodo): void
    {
        $inicio = Carbon::parse($periodo->fecha_inicio);

        // Etapas secuenciales (cada una empieza el día siguiente a la anterior).
        $etapas = [
            ['etapa' => 'carga_evidencias',      'desde' => 0,   'hasta' => 30],
            ['etapa' => 'evaluacion_secretario', 'desde' => 31,  'hasta' => 60],
            ['etapa' => 'evaluacion_cca',        'desde' => 61,  'hasta' => 90],
            ['etapa' => 'apelaciones',           'desde' => 91,  'hasta' => 110],
            ['etapa' => 'evaluacion_jefatura',   'desde' => 111, 'hasta' => 135],
            ['etapa' => 'cierre',                'desde' => 136, 'hasta' => 150],
        ];

        foreach ($etapas as $e) {
            Cronograma::firstOrCreate(
                ['periodo_id' => $periodo->id, 'etapa' => $e['etapa']],
                [
                    'fecha_inicio' => $inicio->copy()->addDays($e['desde'])->toDateString(),
                    'fecha_fin'    => $inicio->copy()->addDays($e['hasta'])->toDateString(),
                ]
            );
        }
    }

    private function crearPlazoFCI(Periodo $periodo, Facultad $fci, User $secretario): void
    {
        PlazoFacultad::firstOrCreate(
            ['periodo_id' => $periodo->id, 'facultad_id' => $fci->id],
            [
                'fecha_limite' => Carbon::today()->addDays(10)->toDateString(),
                'creado_por'   => $secretario->id,
            ]
        );
    }

    /**
     * @return array<int, User> Lista de académicos FCI: índice 0 = academico@ucm.cl.
     */
    private function crearAcademicosFCI(User $academicoBase, Facultad $fci): array
    {
        $perfiles = [
            ['categoria' => 'adjunto',  'linea' => 'docente',      'nota' => 4.2, 'concepto' => 'Muy Bueno'],
            ['categoria' => 'titular',  'linea' => 'investigador', 'nota' => 4.6, 'concepto' => 'Excelente'],
            ['categoria' => 'auxiliar', 'linea' => 'docente',      'nota' => 3.8, 'concepto' => 'Bueno'],
            ['categoria' => 'adjunto',  'linea' => 'mixta',        'nota' => 4.0, 'concepto' => 'Muy Bueno'],
            ['categoria' => 'titular',  'linea' => 'docente',      'nota' => 4.4, 'concepto' => 'Muy Bueno'],
            ['categoria' => 'adjunto',  'linea' => 'docente',      'nota' => 3.6, 'concepto' => 'Bueno'],
        ];

        $this->aplicarPerfil($academicoBase, $perfiles[0]);

        $extras = [
            ['name' => 'María Elena Soto Ríos',       'email' => 'maria.soto@ucm.cl',    'rut' => '12.345.678-9'],
            ['name' => 'Juan Carlos Pérez Muñoz',     'email' => 'juan.perez@ucm.cl',    'rut' => '13.456.789-0'],
            ['name' => 'Andrea Fernanda Lagos Díaz',  'email' => 'andrea.lagos@ucm.cl',  'rut' => '14.567.890-1'],
            ['name' => 'Roberto Esteban Vidal Bravo', 'email' => 'roberto.vidal@ucm.cl', 'rut' => '15.678.901-2'],
            ['name' => 'Camila Paz Núñez Sandoval',   'email' => 'camila.nunez@ucm.cl',  'rut' => '16.789.012-3'],
        ];

        $academicos = [$academicoBase];

        foreach ($extras as $i => $datos) {
            $user = User::firstOrCreate(
                ['email' => $datos['email']],
                [
                    'name'        => $datos['name'],
                    'rut'         => $datos['rut'],
                    'role'        => 'academico',
                    'facultad_id' => $fci->id,
                    'password'    => Hash::make('password'),
                ]
            );
            $this->aplicarPerfil($user, $perfiles[$i + 1] ?? $perfiles[0]);
            $academicos[] = $user;
        }

        return $academicos;
    }

    private function aplicarPerfil(User $user, array $perfil): void
    {
        $user->update([
            'categoria_academica'  => $perfil['categoria'],
            'linea_desarrollo'     => $perfil['linea'],
            'fecha_jerarquizacion' => '2015-06-01',
            'horas_contrato_isem'  => 18,
            'horas_contrato_iisem' => 18,
            'nota_anterior'        => $perfil['nota'],
            'concepto_anterior'    => $perfil['concepto'],
        ]);
    }

    /**
     * Asignaciones de estado:
     *  - índice 0 (academico@ucm.cl) → `en_carga` (tendrá 2 evidencias).
     *  - índice 1                    → `pendiente` + licencia médica activa (aprobada por CCDA).
     *  - índice 2                    → `pendiente` + licencia pendiente de aprobación CCDA.
     *  - índices 3..5                → `pendiente`.
     *
     * @param  array<int, User>  $academicos
     */
    private function crearNominas(Periodo $periodo, array $academicos, User $analista, User $secretario): void
    {
        foreach ($academicos as $i => $u) {
            $datos = [
                'estado' => $i === 0 ? 'en_carga' : 'pendiente',
            ];

            $nomina = Nomina::firstOrCreate(
                ['periodo_id' => $periodo->id, 'user_id' => $u->id],
                $datos
            );

            if ($i === 1) {
                $motivo = 'Licencia médica vigente: reposo por 60 días desde el inicio del período.';
                $inicio = Carbon::today();

                Solicitud::updateOrCreate(
                    [
                        'nomina_id' => $nomina->id,
                        'tipo'      => 'licencia_medica',
                        'estado'    => 'activa',
                    ],
                    [
                        'fecha_inicio'     => $inicio->toDateString(),
                        'fecha_fin'        => $inicio->copy()->addDays(60)->toDateString(),
                        'motivo'           => $motivo,
                        'creado_por'       => $secretario->id,
                        'iniciada_por'     => $secretario->id,
                        'aprobada_por'     => $analista->id,
                        'fecha_aprobacion' => $inicio,
                    ]
                );

                $nomina->update([
                    'con_licencia'         => true,
                    'observacion_licencia' => $motivo,
                ]);
            }

            if ($i === 2) {
                $motivo = 'Director informa licencia médica de 30 días — pendiente revisión CCDA.';

                Solicitud::updateOrCreate(
                    [
                        'nomina_id' => $nomina->id,
                        'tipo'      => 'licencia_medica',
                        'estado'    => 'pendiente_aprobacion',
                    ],
                    [
                        'fecha_inicio' => Carbon::today()->toDateString(),
                        'fecha_fin'    => Carbon::today()->addDays(30)->toDateString(),
                        'motivo'       => $motivo,
                        'creado_por'   => $secretario->id,
                        'iniciada_por' => $secretario->id,
                    ]
                );
            }
        }
    }

    private function crearEvidenciasDemo(Periodo $periodo, User $academico): void
    {
        $nomina = Nomina::where('periodo_id', $periodo->id)
            ->where('user_id', $academico->id)
            ->first();

        if (!$nomina) {
            return;
        }

        $docencia      = CategoriaApa::where('slug', 'docencia')->first();
        $investigacion = CategoriaApa::where('slug', 'investigacion')->first();

        if (!$docencia || !$investigacion) {
            return;
        }

        // Idempotencia: regenerar los archivos dummy si ya existían.
        $disk = Storage::disk('public');
        foreach ($nomina->evidencias as $ev) {
            $disk->delete($ev->ruta);
        }
        $nomina->evidencias()->delete();

        $directorio = "evidencias/{$nomina->id}";

        $archivos = [
            [
                'categoria'   => $docencia,
                'nombre'      => 'planificacion-asignatura-2025.pdf',
                'descripcion' => 'Planificación y syllabus de Programación II (semestre 2025-1).',
            ],
            [
                'categoria'   => $investigacion,
                'nombre'      => 'articulo-revista-ingenieria.pdf',
                'descripcion' => 'Artículo aceptado en revista indexada Scopus (Q3).',
            ],
        ];

        foreach ($archivos as $info) {
            $contenido = $this->generarPdfDummy($info['nombre'], $info['descripcion']);
            $ruta      = "{$directorio}/" . Str::random(16) . '.pdf';

            $disk->put($ruta, $contenido);

            Evidencia::create([
                'nomina_id'      => $nomina->id,
                'categoria_id'   => $info['categoria']->id,
                'nombre_archivo' => $info['nombre'],
                'ruta'           => $ruta,
                'tamano'         => strlen($contenido),
                'mime_type'      => 'application/pdf',
                'subido_por'     => $academico->id,
                'es_apelacion'   => false,
                'descripcion'    => $info['descripcion'],
            ]);
        }
    }

    /** Habilita evaluación CCA: cierra etapa de carga y valida expedientes demo. */
    private function prepararDemoCCA(Periodo $periodo, array $academicos): void
    {
        $ayer = Carbon::yesterday()->toDateString();

        Cronograma::where('periodo_id', $periodo->id)
            ->where('etapa', 'carga_evidencias')
            ->update([
                'fecha_inicio' => Carbon::today()->subDays(60)->toDateString(),
                'fecha_fin'    => $ayer,
            ]);

        // academico@ucm.cl (0) y Roberto Vidal (4) → validados por secretario
        foreach ([0, 4] as $i) {
            if (!isset($academicos[$i])) {
                continue;
            }
            Nomina::where('periodo_id', $periodo->id)
                ->where('user_id', $academicos[$i]->id)
                ->update(['estado' => 'carga_cerrada']);
        }
    }

    /**
     * Genera un PDF dummy mínimo (~1 KB de texto plano con encabezado PDF).
     * No es un PDF totalmente válido para visores estrictos, pero es suficiente
     * para mostrar carga, listado y descarga durante la demo.
     */
    private function generarPdfDummy(string $titulo, string $descripcion): string
    {
        $contenido = "%PDF-1.4\n";
        $contenido .= "% Evidencia dummy para demo APA UCM\n";
        $contenido .= "% Título: {$titulo}\n";
        $contenido .= "% Descripción: {$descripcion}\n";
        $contenido .= "% Generado por DemoSeeder el " . now()->toIso8601String() . "\n";
        $contenido .= str_repeat("% Contenido de demostración - reemplazar con archivo real en producción.\n", 8);
        $contenido .= "%%EOF\n";

        return $contenido;
    }
}
