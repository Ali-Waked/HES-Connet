<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case CONTRACT = 'contract';
    case TEMPORARY = 'temporary';
    case INTERNSHIP = 'internship';
    case VOLUNTEER = 'volunteer';
    case REMOTE = 'remote';
}
