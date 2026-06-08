<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['auth_id','title','content','category_id','views'])]
class Article extends Model
{
    protected function casts():array {
        return [
            'views' => 'integer',
        ];
    }
    public function category():BelongsTo {
        return $this->belongsTo(Category::class);
    }
    public function auth():BelongsTo {
        return $this->belongsTo(Staff::class,'auth_id');
    }
}
