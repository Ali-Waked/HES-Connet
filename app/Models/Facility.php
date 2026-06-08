<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Spatie\Translatable\HasTranslations;

#[Fillable(['name','latitude','longitude','facility_type','organization_id','parent_id'])]
class Facility extends Model
{
    use HasUuids, HasTranslations;

    public array $translatable = ['name'];
    public function organization(): BelongsTo {
        return $this->belongsTo(Organization::class);
    }

    public function parent(): BelongsTo {
        return $this->belongsTo(Facility::class);
    }

    public function children(): HasMany {
        return $this->hasMany(Facility::class);
    }

    public function facilityImages(): HasMany {
        return $this->hasMany(FacilityImage::class);
    }

    public function facilityDocuments(): HasMany {
        return $this->hasMany(FacilityDocument::class);
    }
}
