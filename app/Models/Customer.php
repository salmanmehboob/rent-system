<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 
        'name', 
        'mobile_no', 
        'cnic', 
        'address', 
        'status'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
       
    }


    public function rooms()
    {
         return $this->hasManyThrough(
            RoomShop::class,
            Agreement::class,
            'customer_id', // Foreign key on agreements table
            'id',          // Foreign key on room_shops table
            'id',          // Local key on customers table
            'room_shop_id' // Local key on agreements table
        )->where('room_shops.availability', 0);
       
    }

    public function agreements()
    {
        return $this->hasMany(Agreement::class);
       
    }

    public function activeAgreement()
    {
        return $this->hasOne(Agreement::class)->where('status', 'active');
    }


    public function witnesses()
    {
        return $this->hasMany(Witness::class);
       
    }

    public function transations()
    {
        return $this->hasMany(Transaction::class);
    }

    protected static function booted()
    {
        static::deleting(function ($customer) {
            // Reset all rooms assigned to this customer
            foreach ($customer->rooms as $room) {
                $room->update([
                    'customer_id' => null,
                    'availability' => 1 // Available
                ]);
            }
        });
    }

}
