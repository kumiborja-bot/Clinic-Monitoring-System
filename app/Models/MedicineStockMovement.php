<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'transaction_date',
        'type',
        'quantity',
        'supplier',
        'requester',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity' => 'integer',
    ];

    public function medicine()
    {
        return $this->belongsTo(
            Medicine::class
        );
    }
}