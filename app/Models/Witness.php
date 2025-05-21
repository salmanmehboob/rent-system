<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Witness extends Model
{
  use HasFactory;

   protected $fillable = [
    'name',
    'mobile_no',
    'cnic',
    'address'
   ];

  
   public function rooms()
   {
      return $this->hasMany(RoomShop::class);
   }

    public function customer()
   {
      return $this->belongsTo(Customer::class);
   }
}
