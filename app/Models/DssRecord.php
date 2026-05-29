<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DssRecord extends Model
{
    use HasFactory;

    protected $table = 'dss_records';

    protected $fillable = [
        'diagnosis',
        'suggestion',
        'follow_up',
        'medication',
        'duration_unit',
        'duration_value',
    ];
}
