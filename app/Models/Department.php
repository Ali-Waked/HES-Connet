<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Spatie\Translatable\HasTranslations;

#[Fillable(['name','facility_id','head_id'])]
class Department extends Model
{
    use HasUlids, HasTranslations;

    public array $translatable = ['name'];

    public function head(): BelongsTo {
        return $this->belongsTo(Staff::class,'head_id');
    }

    public function facility(): BelongsTo {
        return $this->belongsTo(Facility::class);
    }
}
