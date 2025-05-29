<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class BuildingReport extends Model
{
    use HasFactory, SoftDeletes;
    
    Protected $fillable = [
        'building_id',
        'total_rooms',
        'total_shops',
        'available_rooms',
        'available_shops',
        'total_rented_roomshops'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
