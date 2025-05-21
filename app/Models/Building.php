<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Building extends Model
{
  use HasFactory;

   protected $fillable = [
    'name',
    'address',
    'contact',
    'contact_person'
   ];

  
   public function rooms()
   {
      return $this->hasMany(Room::class);
   }

    public function customers()
   {
      return $this->hasMany(Customer::class);
   }

   public function transations()
   {
      return $this->hasMany(Transaction::class);
   }
}
