<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('facility_id', 'medicine_id', 'is_available', 'stock', 'price')]
class PharmacyMedicine extends Model
{
    use HasUuids;

    //  protected $table = 'pharmacy_medicines';
    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'stock' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function isInStock(): bool
    {
        return $this->is_available && $this->stock > 0;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity > $this->stock) {
            throw new \Exception('Insufficient stock');
        }

        $this->decrement('stock', $quantity);
    }

    public function increaseStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('stock', '>', 0);
    }
}
