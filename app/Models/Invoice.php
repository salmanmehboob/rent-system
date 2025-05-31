<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'building_id',
        'customer_id',
        'agreement_id',
        'year',
        'month',
        'rent_amount',
        'dues',
        'remaining',
        'paid',
        'total',
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

     public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
