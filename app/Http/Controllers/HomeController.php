<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\RoomShop;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Agreement;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

        // Call the check:agreements command to check active and expired agreements
        Artisan::call('check:agreements');
        $buildingsCount = Building::count();
        $roomhopsCount = RoomShop::count();
        $customersCount = Customer::count();
        $invoicesCount = Invoice::count();
        // Get agreements that have expired (end_date < today)
        $expiredAgreements = Agreement::with(['customer', 'roomShops'])
            ->where('end_date', '<', now()->toDateString())
            ->orderBy('end_date', 'desc')
            ->get();

        // Get agreements that are expiring in the current month
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $expiringThisMonth = Agreement::with(['customer', 'roomShops'])
            ->whereMonth('end_date', $currentMonth)
            ->whereYear('end_date', $currentYear)
            ->where('end_date', '>=', now()->toDateString())
            ->orderBy('end_date', 'asc')
            ->get();

        // Pass to dashboard view


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
            ->where('type', 'Current')
            ->groupBy('customer_id')
            ->having('total_due', '>', 0)
            ->orderByDesc('total_due')
            ->limit(10)
            ->get()
            ->map(function ($invoice) {
                return [
                    'customer_name' => $invoice->customer->name ?? 'Unknown',
                    'total_due' => (float) $invoice->total_due
                ];
            });

        // dd($topCustomers);

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
                'topCustomers',
            'expiredAgreements',
            'expiringThisMonth'
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
