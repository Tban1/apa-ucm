<?php

namespace App\Enums;

enum EstadoNominaEnum: string
{
    case Pendiente     = 'pendiente';
    case EnCarga       = 'en_carga';
    case CargaCerrada  = 'carga_cerrada';
    case EnEvaluacion  = 'en_evaluacion';
    case Evaluado      = 'evaluado';
    case Apelado       = 'apelado';
    case Cerrado       = 'cerrado';

    public function label(): string
    {
        return match($this) {
            self::Pendiente    => 'Pendiente',
            self::EnCarga      => 'En carga de evidencias',
            self::CargaCerrada => 'Carga cerrada',
            self::EnEvaluacion => 'En evaluación',
            self::Evaluado     => 'Evaluado',
            self::Apelado      => 'Apelado',
            self::Cerrado      => 'Cerrado',
        };
    }
}
