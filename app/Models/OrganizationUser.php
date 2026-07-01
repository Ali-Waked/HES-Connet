<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationRole;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $user_id
 * @property int $organization_id
 * @property OrganizationRole $status
 * @property-read Organization $organization
 * @property-read User $user
 */
#[Table('organization_user')]
#[Fillable('user_id', 'organization_id', 'status')]
class OrganizationUser extends Model
{
    use Auditable;

    protected function casts(): array
    {
        return [
            'status' => OrganizationRole::class,
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
