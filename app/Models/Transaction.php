<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    Protected $fillable = [
        'invoice_id',
        'year',
        'month',
        'paid',
        'dues',
        'note'
    ];
}
