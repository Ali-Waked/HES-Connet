<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case OWNER = 'owner';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case VIEWER = 'viewer';
}
