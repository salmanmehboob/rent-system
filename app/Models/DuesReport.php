<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DuesReport extends Model
{
    use HasFactory, SoftDeletes;
    
    Protected $fillable = [
        'building_id',
        'room_shop_id',
        'customer_id',
        'total_paid',
        'total_remaining'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

}
