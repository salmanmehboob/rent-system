<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agreement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'duration',
        'monthly_rent',
        'start_date',
        'end_date',
        'status'
    ];


    // public function rooms()
    // {
    //     return RoomShop::whereIn('id', json_decode($this->room_shop_ids ?? '[]'))->get();
    // }

    public function roomShops()
    {
        return $this->belongsToMany(RoomShop::class, 'agreement_room_shop');
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
       
    }

    public function witnesses()
    {
        return $this->belongsToMany(Witness::class, 'agreement_witness')
                    ->withTimestamps();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // public function transactions()
    // {
    //     return $this->hasMany(Transaction::class);
    // }

    protected static function booted()
    {
        static::created(function ($agreement) {
            foreach ($agreement->roomShops as $room) {
                $room->update([
                    'customer_id' => $agreement->customer_id,
                    'availability' => 0
                ]);
            }
        });

        static::updating(function ($agreement) {
            if ($agreement->isDirty('status') && $agreement->status === 'inactive') {
                foreach ($agreement->roomShops as $room) {
                    $room->update([
                        'availability' => 1,
                        'customer_id' => null
                    ]);
                }
            }
        });

        static::deleting(function ($agreement) {
            foreach ($agreement->roomShops as $room) {
                $room->update([
                    'availability' => 1,
                    'customer_id' => null
                ]);
            }
        });
    }

}
