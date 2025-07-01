<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use DB;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate all invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();
        $month = $now->format('F');
        $year = $now->year;
        $monthNumber = $now->month;


        $buildings = Building::all();
        $totalGenerated = 0;
        $totalSkipped = 0;

        foreach ($buildings as $building) {
            DB::beginTransaction();

            try {
                $customers = Customer::with(['agreements' => function($query) use ($monthNumber, $year) {
                    $query->where('status', 'Active')
                          ->whereDate('start_date', '<=', Carbon::createFromDate($year, $monthNumber, 1)->endOfMonth());
                }, 'agreements.roomShops'])
                ->where('building_id', $building->id)
                ->whereHas('agreements', function($query) use ($monthNumber, $year) {
                    $query->where('status', 'Active')
                          ->whereDate('start_date', '<=', Carbon::createFromDate($year, $monthNumber, 1)->endOfMonth());
                })->get();

                foreach ($customers as $customer) {
                    $agreement = $customer->agreements->first();
                    if (!$agreement) {
                        $totalSkipped++;
                        continue;
                    }

                    $existingInvoice = Invoice::where('customer_id', $customer->id)
                        ->where('month', $month)
                        ->where('year', $year)
                        ->first();

                    if ($existingInvoice) {
                        $totalSkipped++;
                        continue;
                    }

                    $roomCount = $agreement->roomShops->count();
                    $monthlyRent = $agreement->monthly_rent;
                    $rentAmount = $monthlyRent * $roomCount;

                    $lastInvoice = Invoice::where('customer_id', $customer->id)
                        ->where('is_active', true)
                        ->orderByDesc('id')
                        ->first();

                    $previousDues = $lastInvoice ? $lastInvoice->remaining : 0;
                    $total = $rentAmount + $previousDues;

                    $newInvoiceDate = Carbon::createFromFormat('d-F-Y H:i:s', '01-' . $month . '-' . $year . ' 00:00:00');
                    $existingInvoices = Invoice::where('customer_id', $customer->id)->get();

                    if ($existingInvoices->count()) {
                        $latestInvoice = $existingInvoices->map(function ($invoice) {
                            return [
                                'model' => $invoice,
                                'date' => Carbon::createFromFormat('d-F-Y H:i:s', '01-' . $invoice->month . '-' . $invoice->year . ' 00:00:00'),
                            ];
                        })->sortByDesc('date')->first();

                        if ($newInvoiceDate->gt($latestInvoice['date'])) {
                            $type = 'Current';

                            Invoice::where('customer_id', $customer->id)
                                ->where('type', 'Current')
                                ->update(['type' => 'Previous', 'status' => 'Dues Adjusted']);
                        } else {
                            $type = 'Previous';
                        }
                    } else {
                        $type = 'Current';
                    }

                    Invoice::create([
                        'building_id'  => $building->id,
                        'customer_id'  => $customer->id,
                        'agreement_id' => $agreement->id,
                        'month'        => $month,
                        'year'         => $year,
                        'rent_amount'  => $rentAmount,
                        'dues'         => $previousDues,
                        'paid'         => 0,
                        'total'        => $total,
                        'remaining'    => $total,
                        'type'         => $type,
                        'status'       => $type === 'Previous' ? 'Dues Adjusted' : 'Unpaid',
                        'is_active'    => true,
                    ]);

                    $totalGenerated++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed for building: {$building->name} => " . $e->getMessage());
            }
        }

        $this->info("✅ {$totalGenerated} invoices generated. 🚫 {$totalSkipped} skipped.");
        return 0;
    }

  

}
