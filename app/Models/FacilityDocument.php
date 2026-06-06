<?php

namespace App\Models;

use App\Enums\FacilityDocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['facility_id','document_type','status','file_url'])]
class FacilityDocument extends Model
{
    protected function casts(): array {
        return [
            'status' => FacilityDocumentStatus::class,
        ];
    }
}
