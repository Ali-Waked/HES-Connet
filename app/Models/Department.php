<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name','facility_id','head_id'])]
class Department extends Model
{
    use HasUlids;

    public function head(): BelongsTo {
        return $this->belongsTo(Staff::class,'head_id');
    }

    public function facility(): BelongsTo {
        return $this->belongsTo(Facility::class);
    }
}
