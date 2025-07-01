<?php

namespace App\Models;

use Illuminate\Support\Carbon;
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
        'status',
        'type'
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $monthMap = [
                'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
                'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
                'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
            ];

            $monthName = trim($invoice->month);
            $monthNum = $monthMap[$monthName] ?? null;

            if ($monthNum && is_numeric($invoice->year)) {
                $invoice->invoice_date = Carbon::create($invoice->year, $monthNum, 1)->startOfMonth();
            }
        });
    }
}
