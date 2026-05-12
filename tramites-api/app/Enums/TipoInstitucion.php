<?php

namespace App\Enums;

enum TipoInstitucion: string
{
    case MINISTERIO = 'MINISTERIO';
    case ALCALDIA   = 'ALCALDIA';
    case AUTONOMA   = 'AUTONOMA';

    public function label(): string
    {
        return match($this) {
            self::MINISTERIO => 'Ministerio',
            self::ALCALDIA   => 'Alcaldía',
            self::AUTONOMA   => 'Entidad Autónoma',
        };
    }
}
