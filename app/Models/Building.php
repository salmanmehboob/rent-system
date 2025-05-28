<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Building extends Model
{
  use HasFactory, SoftDeletes;

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

   public function invoices()
   {
      return $this->hasMany(Transaction::class);
   }
}
