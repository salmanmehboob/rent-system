<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;
    
    Protected $fillable = [
        'invoice_id',
        'year',
        'month',
        'paid',
        'remaining',
        'note'
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
