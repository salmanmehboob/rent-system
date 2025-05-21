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

    public function agreement()
    {
        return $this->hasOne(Agreement::class);
    }

    protected static function booted()
    {
        static::updating(function ($roomshop) {
            if ($roomshop->isDirty('availability')) {
                // When making available
                if ($roomshop->availability == 1) {
                    $roomshop->customer_id = null;
                    
                    if ($roomshop->agreement) {
                        // Don't set room_shop_id to null - instead:
                        $roomshop->agreement->update([
                            'status' => 'inactive',
                            'end_date' => now()
                            // Keep the room_shop_id value
                        ]);
                    }
                }
                // When making unavailable
                else {
                    if ($roomshop->agreement) {
                        $roomshop->agreement->update([
                            'status' => 'active',
                            'start_date' => now()
                        ]);
                        $roomshop->customer_id = $roomshop->agreement->customer_id;
                    }
                }
            }
        });
    } 

    public function scopeAvailable($query)
    {
        return $query->where('availability', 1)->whereNull('customer_id')->whereDoesntHave('agreement', function($q) {
            $q->where('status', 'active');
        });
    }

    
}
