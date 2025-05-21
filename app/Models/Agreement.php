<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_shop_id',
        'customer_id',
        'duration',
        'monthly_rent',
        'start_date',
        'end_date',
        'status'
    ];


    public function room()
    {
        return $this->belongsTo(RoomShop::class, 'room_shop_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
       
    }

    protected static function booted()
    {
        static::created(function ($agreement) {
            $agreement->room->update([
                'customer_id' => $agreement->customer_id,
                'availability' => 0
            ]);
        });

        static::updated(function ($agreement) {
            // When agreement is reactivated
            if ($agreement->isDirty('status') && $agreement->status == 'active') {
                $agreement->room->update([
                    'customer_id' => $agreement->customer_id,
                    'availability' => 0
                ]);
            }
        });

        static::deleting(function ($agreement) {
            if ($agreement->room) {
                $agreement->room->update([
                    'customer_id' => null,
                    'availability' => 1
                ]);
            }
        });
    }
}
