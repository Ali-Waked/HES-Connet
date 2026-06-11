<?php

namespace App\Enums;

enum OrganizationType: string
{
    case GOVERNMENT = 'government';
    case UN_AGENCY = 'un_agency';
    case INTERNATIONAL_NGO = 'international_ngo';
    case LOCAL_NGO = 'local_ngo';
    case PRIVATE = 'private';

    // public function label(): string
    // {
    //     return match ($this) {
    //         self::GOVERNMENT => __('organization_types.government'),
    //         self::UN_AGENCY => __('organization_types.un_agency'),
    //         self::INTERNATIONAL_NGO => __('organization_types.international_ngo'),
    //         self::LOCAL_NGO => __('organization_types.local_ngo'),
    //         self::PRIVATE => __('organization_types.private'),
    //     };
    // }
}
