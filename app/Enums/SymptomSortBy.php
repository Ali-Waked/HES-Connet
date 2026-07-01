<?php

declare(strict_types=1);

namespace App\Enums;

enum SymptomSortBy: string
{
    case NAME = 'name';
    case CREATED_AT = 'created_at';
    case IS_ACTIVE = 'is_active';
}
