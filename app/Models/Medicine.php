<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'supplier',
        'unit',
        'stock_quantity',
        'reorder_level',
        'expiry_date',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'expiry_date' => 'date',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(MedicineLog::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(MedicineStockMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function totalDispensed(): int
    {
        return (int) $this->logs()->sum('quantity');
    }
}