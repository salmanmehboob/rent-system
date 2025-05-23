<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_shop_ids',
        'customer_id',
        'duration',
        'monthly_rent',
        'start_date',
        'end_date',
        'status'
    ];


    public function rooms()
    {
        return RoomShop::whereIn('id', json_decode($this->room_shop_ids ?? '[]'))->get();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
       
    }

    // public function transactions()
    // {
    //     return $this->hasMany(Transaction::class);
    // }

    protected static function booted()
    {
        static::created(function ($agreement) {
            $roomIds = json_decode($agreement->room_shop_ids ?? '[]');
            RoomShop::whereIn('id', $roomIds)->update([
                'customer_id' => $agreement->customer_id,
                'availability' => 0
            ]);
        });

        static::updated(function ($agreement) {
            if ($agreement->isDirty('status') && $agreement->status == 'active') {
                $roomIds = json_decode($agreement->room_shop_ids ?? '[]');
                RoomShop::whereIn('id', $roomIds)->update([
                    'customer_id' => $agreement->customer_id,
                    'availability' => 0
                ]);
            }
        });

        static::deleting(function ($agreement) {
            $roomIds = json_decode($agreement->room_shop_ids ?? '[]');
            RoomShop::whereIn('id', $roomIds)->update([
                'customer_id' => null,
                'availability' => 1
            ]);
        });

    }
}
