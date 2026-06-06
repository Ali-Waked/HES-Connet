<?php

namespace App\Enums;

enum FacilityDocumentStatus : string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

}
