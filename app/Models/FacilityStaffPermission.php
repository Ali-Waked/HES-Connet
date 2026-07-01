<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['facility_staff_id', 'permission_id', 'enabled'])]
class FacilityStaffPermission extends Model
{
    use Auditable;

    protected $table = 'facility_staff_permissions';

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function facilityStaff(): BelongsTo
    {
        return $this->belongsTo(FacilityStaff::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
