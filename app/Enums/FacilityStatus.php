<?php

namespace App\Enums;

enum FacilityStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case TEMPORARILY_CLOSED = 'temporarily_closed';
    case PERMANENTLY_CLOSED = 'permanently_closed';
}
