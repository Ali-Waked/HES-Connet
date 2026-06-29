<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'message',
    'status',
])]
class ContactMessage extends Model
{
    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }
}
