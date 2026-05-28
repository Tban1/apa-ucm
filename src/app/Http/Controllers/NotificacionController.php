<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class NotificacionController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $notificaciones = $user->notificaciones()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id'         => $n->id,
                'tipo'       => $n->tipo,
                'titulo'     => $n->titulo,
                'mensaje'    => $n->mensaje,
                'leida'      => $n->leida,
                'url'        => $n->url,
                'created_at' => $n->created_at->format('d/m/Y H:i'),
            ]);

        // Marcar todas como leídas al abrir la bandeja
        $user->notificaciones()->noLeidas()->update(['leida' => true]);

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
        ]);
    }
}
