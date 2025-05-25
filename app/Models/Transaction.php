<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'customer_id',
        'agreement_id',
        'year',
        'month',
        'rent_amount',
        'previous_dues',
        'sub_total',
        'payable_amount',
        'current_dues',
        'status'
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
        return $this->belongsTo(Agreement::class);
    }
}
