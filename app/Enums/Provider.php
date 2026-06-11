<?php

namespace App\Enums;

enum Provider: string
{
    case LOCAL = 'local';
    case GOOGLE = 'google';
}
