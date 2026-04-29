<?php

declare(strict_types=1);

namespace App\Enums;

enum AccionAuditoria: string
{
    case CREAR = 'CREAR';
    case EDITAR = 'EDITAR';
    case ELIMINAR = 'ELIMINAR';
    case RESTAURAR = 'RESTAURAR';
    case CONSULTAR = 'CONSULTAR';
}
