<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class RoomShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'customer_id',
        'type',
        'no',
        'availability'
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // public function agreement()
    // {
    //     return $this->hasOne(Agreement::class);
    // }

    public function agreements()
    {
        return $this->belongsToMany(Agreement::class, 'agreement_room_shop');
    }


    protected static function booted()
    {
        static::updating(function ($roomshop) {
            if ($roomshop->isDirty('availability') && $roomshop->availability == 1) {
                // Deactivate all agreements linked to this room
                foreach ($roomshop->agreements as $agreement) {
                    $agreement->update([
                        'status' => 'inactive',
                        'end_date' => now()
                    ]);
                }

                $roomshop->customer_id = null;
            }
        });

        static::deleting(function ($roomshop) {
            // Deactivate agreements
            foreach ($roomshop->agreements as $agreement) {
                $agreement->update([
                    'status' => 'inactive',
                    'end_date' => now()
                ]);
            }
        });
    }


   public function scopeAvailable($query)
    {
        return $query->where('availability', 1)
            ->whereNull('customer_id');
    }


    
}
