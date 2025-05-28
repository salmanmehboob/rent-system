<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;



class Witness extends Model
{
  use HasFactory, SoftDeletes;

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

    public function customers()
   {
      return $this->belongsToMany(Customer::class,'customer_witness');
   }

   public function agreements()
   {
      return $this->belongsToMany(Agreement::class, 'agreement_witness')
                  ->withTimestamps();
   }

}
