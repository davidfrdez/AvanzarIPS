<?php

declare(strict_types=1);

namespace App\Enums;

enum EstadoConsentimiento: string
{
    case PENDIENTE = 'Pendiente';
    case FIRMADO = 'Firmado';
    case RECHAZADO = 'Rechazado';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
