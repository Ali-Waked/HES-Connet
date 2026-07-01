<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
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
    use Auditable;

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }
}
