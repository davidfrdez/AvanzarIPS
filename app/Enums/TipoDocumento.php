<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoDocumento: string
{
    case CC = 'CC';
    case TI = 'TI';
    case CE = 'CE';
    case RC = 'RC';
    case PA = 'PA';
    case PE = 'PE';

    public function label(): string
    {
        return match ($this) {
            self::CC => 'Cédula de Ciudadanía',
            self::TI => 'Tarjeta de Identidad',
            self::CE => 'Cédula de Extranjería',
            self::RC => 'Registro Civil',
            self::PA => 'Pasaporte',
            self::PE => 'Permiso Especial de Permanencia',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
