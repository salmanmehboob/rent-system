<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use HasFactory, SoftDeletes;

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
        return RoomShop::whereHas('agreements', function ($q) {
            $q->where('customer_id', $this->id)->where('status', 'active');
        })->get();
    }
 

    public function agreements()
    {
        return $this->hasMany(Agreement::class);
       
    }

    // public function activeAgreement()
    // {
    //     return $this->hasOne(Agreement::class)->where('status', 'active');
    // }


    public function witnesses()
    {
        return $this->belongsToMany(Witness::class, 'customer_witness');
       
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    protected static function booted()
    {
        static::deleting(function ($customer) {
            // Deactivate their agreements
            foreach ($customer->agreements as $agreement) {
                $agreement->update(['status' => 'inactive']);
            }

            // Free rooms
            foreach ($customer->rooms() as $room) {
                $room->update([
                    'customer_id' => null,
                    'availability' => 1
                ]);
            }
        });
    }

    public function reports()
    {
        return $this->hasMany(CustomerReport::class);
    }


}
