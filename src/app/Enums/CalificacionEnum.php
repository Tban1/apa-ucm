<?php

namespace App\Enums;

enum CalificacionEnum: string
{
    case MuyBueno  = 'muy_bueno';
    case Bueno     = 'bueno';
    case Aceptable = 'aceptable';
    case Deficiente = 'deficiente';

    public function label(): string
    {
        return match($this) {
            self::MuyBueno   => 'Muy Bueno',
            self::Bueno      => 'Bueno',
            self::Aceptable  => 'Aceptable',
            self::Deficiente => 'Deficiente',
        };
    }

    public static function desdePuntaje(int $puntaje): self
    {
        return match(true) {
            $puntaje >= 90 => self::MuyBueno,
            $puntaje >= 75 => self::Bueno,
            $puntaje >= 60 => self::Aceptable,
            default        => self::Deficiente,
        };
    }
}
