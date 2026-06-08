<?php

namespace App\Enums;

enum FacilityType: string
{
    case HOSPITAL = 'hospital';

    case CLINIC = 'clinic';

    case PHARMACY = 'pharmacy';

    case MEDICAL_POINT = 'medical_point';
}