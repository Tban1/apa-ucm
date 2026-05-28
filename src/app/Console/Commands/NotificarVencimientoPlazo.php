<?php

namespace App\Console\Commands;

use App\Models\Nomina;
use App\Models\Notificacion;
use App\Models\PlazoFacultad;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class NotificarVencimientoPlazo extends Command
{
    protected $signature   = 'apa:notificar-vencimiento';
    protected $description = 'Envía notificación a académicos cuyo plazo de entrega vence mañana';

    public function handle(): int
    {
        $manana = now()->addDay()->toDateString();

        // Plazos generales de facultad que vencen mañana y no están cerrados formalmente
        $plazos = PlazoFacultad::whereDate('fecha_limite', $manana)
            ->whereNull('cerrado_en')
            ->with(['periodo', 'facultad'])
            ->get();

        $totalNotificados = 0;

        foreach ($plazos as $plazo) {
            $nominas = Nomina::with('academico')
                ->where('periodo_id', $plazo->periodo_id)
                ->whereHas('academico', fn ($q) => $q->where('facultad_id', $plazo->facultad_id))
                ->whereIn('estado', ['pendiente', 'en_carga'])
                ->get();

            $notificaciones = $nominas->map(fn ($n) => [
                'id'         => Str::uuid(),
                'user_id'    => $n->user_id,
                'tipo'       => 'vencimiento_plazo',
                'titulo'     => 'Plazo de entrega vence mañana',
                'mensaje'    => "El plazo para cargar sus evidencias en el período \"{$plazo->periodo->nombre} {$plazo->periodo->anio}\" vence mañana ({$manana}). Asegúrese de haber subido toda la documentación requerida.",
                'leida'      => false,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            if (!empty($notificaciones)) {
                Notificacion::insert($notificaciones);
                $totalNotificados += count($notificaciones);
            }
        }

        // Plazos especiales por licencia médica que vencen mañana
        $nominasLicencia = Nomina::with(['academico', 'periodo'])
            ->whereDate('plazo_licencia', $manana)
            ->whereNotNull('plazo_licencia')
            ->whereIn('estado', ['pendiente', 'en_carga'])
            ->get();

        $notifLicencia = $nominasLicencia->map(fn ($n) => [
            'id'         => Str::uuid(),
            'user_id'    => $n->user_id,
            'tipo'       => 'vencimiento_plazo',
            'titulo'     => 'Plazo especial vence mañana',
            'mensaje'    => "Su plazo especial de entrega de evidencias (caso de licencia médica) vence mañana ({$manana}). Asegúrese de haber subido toda la documentación requerida.",
            'leida'      => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        if (!empty($notifLicencia)) {
            Notificacion::insert($notifLicencia);
            $totalNotificados += count($notifLicencia);
        }

        $this->info("Notificaciones enviadas: {$totalNotificados}");
        return self::SUCCESS;
    }
}
