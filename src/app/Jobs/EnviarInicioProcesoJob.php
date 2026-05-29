<?php

namespace App\Jobs;

use App\Mail\InicioProcesoMail;
use App\Models\Cronograma;
use App\Models\Notificacion;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnviarInicioProcesoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $periodoId,
        public string $userId,
        public ?string $notificacionId = null,
    ) {}

    public function handle(): void
    {
        $periodo = Periodo::with('cronogramas')->find($this->periodoId);
        $user    = User::find($this->userId);

        if (!$periodo || !$user || !$user->email) {
            $this->marcarNotificacion('fallido');

            return;
        }

        $orden = array_flip(Cronograma::ETAPAS);
        $cronograma = $periodo->cronogramas
            ->sortBy(fn ($c) => $orden[$c->etapa] ?? 99)
            ->map(fn ($c) => [
                'etapa'        => Cronograma::etiqueta($c->etapa),
                'fecha_inicio' => $c->fecha_inicio->format('d/m/Y'),
                'fecha_fin'    => $c->fecha_fin->format('d/m/Y'),
            ])
            ->values()
            ->all();

        try {
            Mail::to($user->email)->send(new InicioProcesoMail(
                $periodo,
                $cronograma,
                config('app.url'),
            ));
            $this->marcarNotificacion('enviado');
        } catch (\Throwable) {
            $this->marcarNotificacion('fallido');
        }
    }

    private function marcarNotificacion(string $estado): void
    {
        if (!$this->notificacionId) {
            return;
        }

        Notificacion::where('id', $this->notificacionId)->update([
            'estado_envio' => $estado,
            'fecha_envio'  => now(),
        ]);
    }
}
