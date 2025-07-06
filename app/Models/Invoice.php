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
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

     public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the formatted rent amount
     */
    public function getFormattedRentAmountAttribute()
    {
        return number_format($this->rent_amount, 2);
    }

    /**
     * Get the formatted dues amount
     */
    public function getFormattedDuesAttribute()
    {
        return number_format($this->dues, 2);
    }

    /**
     * Get the formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }

    /**
     * Get the formatted paid amount
     */
    public function getFormattedPaidAttribute()
    {
        return number_format($this->paid, 2);
    }

    /**
     * Get the formatted remaining amount
     */
    public function getFormattedRemainingAttribute()
    {
        return number_format($this->remaining, 2);
    }

    /**
     * Calculate the total amount (rent + dues)
     */
    public function calculateTotal()
    {
        return $this->rent_amount + $this->dues;
    }

    /**
     * Calculate the remaining amount (total - paid)
     */
    public function calculateRemaining()
    {
        return max(0, $this->total - $this->paid);
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
