<?php

declare(strict_types=1);

namespace App\Enums;

enum Sexo: string
{
    case M = 'M';
    case F = 'F';

    public function label(): string
    {
        return match ($this) {
            self::M => 'Masculino',
            self::F => 'Femenino',
        };
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(static fn (self $c): string => $c->value, self::cases());
    }
}
