<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\RoomShop;
use App\Models\Customer;
use App\Models\Invoice;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $buildingsCount = Building::count();
        $roomhopsCount = RoomShop::count();
        $customersCount = Customer::count();
        $invoicesCount = Invoice::count();

        // Determine if print button should be shown (true if invoices exist)
        $canPrintInvoices = $invoicesCount > 0;

        // Get latest invoice month and year (optional dashboard info)
        $latestInvoice = Invoice::latest('created_at')->first();
        $latestMonth = $latestInvoice?->month;
        $latestYear = $latestInvoice?->year;

        // Count invoices in latest month and year (optional)
        $lastBillsCount = 0;
        if ($latestMonth && $latestYear) {
            $lastBillsCount = Invoice::where('month', $latestMonth)
                                      ->where('year', $latestYear)
                                      ->count();
        }

        $total = Invoice::sum('total');
        $dues = Invoice::sum('remaining');
        $paid = Invoice::sum('paid');
        $invoices = Invoice::select('paid', 'remaining')->get();
        $roomshops = RoomShop::where('availability', 1)->count();

        $topCustomers = Invoice::with('customer:id,name')
            ->select('customer_id', DB::raw('SUM(remaining) as total_due'))
            ->groupBy('customer_id')
            ->orderByDesc('total_due')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'customer_name' => $invoice->customer->name ?? 'Unknown',
                    'total_due' => (float) $invoice->total_due
                ];
            });


        return view('home', compact(
            'buildingsCount',
            'roomhopsCount',
            'customersCount',
            'invoicesCount',
            'lastBillsCount',
            'latestMonth',
            'latestYear',
            'canPrintInvoices',
            'total',
            'dues',
            'paid',
            'invoices',
            'roomshops',
            'topCustomers'
        ));
    }


    //   public function index()
    // {
    //     $latestMonth = Invoice::latest('created_at')->value('month');
    //     $latestYear = Invoice::latest('created_at')->value('year');

    //     $totalInvoices = Invoice::where('month', $latestMonth)->where('year', $latestYear)->count();
    //     $totalAmount = Invoice::where('month', $latestMonth)->where('year', $latestYear)->sum('total');

    //     return view('dashboard', compact('totalInvoices', 'totalAmount', 'latestMonth', 'latestYear'));
    // }
}
