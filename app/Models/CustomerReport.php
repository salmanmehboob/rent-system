<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CustomerReport extends Model
{
    use HasFactory, SoftDeletes;
    
   protected $fillable = [
    'customer_id',
    'month',
    'rent',
    'paid_amount',
    'dues',
    'payment_date'
   ];


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
