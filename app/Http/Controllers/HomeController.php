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
                    'total_due' => (float)$invoice->total_due
                ];
            });

        // Additional data for report summary graph
        $monthlyCollection = Invoice::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(paid) as total_paid'),
            DB::raw('SUM(remaining) as total_remaining'),
            DB::raw('COUNT(*) as invoice_count')
        )
        ->whereYear('created_at', '>=', now()->subMonths(6)->year)
        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) {
            $monthName = date('M', mktime(0, 0, 0, $item->month, 1));
            return [
                'period' => $monthName . ' ' . $item->year,
                'paid' => (float)$item->total_paid,
                'remaining' => (float)$item->total_remaining,
                'total' => (float)($item->total_paid + $item->total_remaining),
                'count' => $item->invoice_count
            ];
        });

        // Building-wise collection data
        $buildingCollection = Invoice::with('customer.agreements.roomShops.building')
            ->select(
                'customer_id',
                DB::raw('SUM(paid) as total_paid'),
                DB::raw('SUM(remaining) as total_remaining')
            )
            ->groupBy('customer_id')
            ->get()
            ->groupBy(function ($invoice) {
                $building = $invoice->customer->agreements->first()->roomShops->first()->building ?? null;
                return $building ? $building->name : 'Unknown Building';
            })
            ->map(function ($group) {
                return [
                    'total_paid' => $group->sum('total_paid'),
                    'total_remaining' => $group->sum('total_remaining'),
                    'total' => $group->sum('total_paid') + $group->sum('total_remaining')
                ];
            })
            ->sortByDesc('total');

        // Collection vs Dues comparison for the last 6 months
        $collectionTrend = Invoice::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(paid) as collection'),
            DB::raw('SUM(remaining) as dues')
        )
        ->whereYear('created_at', '>=', now()->subMonths(6)->year)
        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) {
            $monthName = date('M', mktime(0, 0, 0, $item->month, 1));
            return [
                'period' => $monthName . ' ' . $item->year,
                'collection' => (float)$item->collection,
                'dues' => (float)$item->dues
            ];
        });

        // Expired Agreements Data for Charts
        $expiredAgreementsData = Agreement::select(
            DB::raw('YEAR(end_date) as year'),
            DB::raw('MONTH(end_date) as month'),
            DB::raw('COUNT(*) as expired_count')
        )
        ->where('end_date', '<', now()->toDateString())
        ->whereYear('end_date', '>=', now()->subMonths(6)->year)
        ->groupBy(DB::raw('YEAR(end_date)'), DB::raw('MONTH(end_date)'))
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) {
            $monthName = date('M', mktime(0, 0, 0, $item->month, 1));
            return [
                'period' => $monthName . ' ' . $item->year,
                'expired_count' => (int)$item->expired_count
            ];
        });

        // Expiring Agreements Data for Charts (next 3 months)
        $expiringAgreementsData = Agreement::select(
            DB::raw('YEAR(end_date) as year'),
            DB::raw('MONTH(end_date) as month'),
            DB::raw('COUNT(*) as expiring_count')
        )
        ->where('end_date', '>=', now()->toDateString())
        ->where('end_date', '<=', now()->addMonths(3)->toDateString())
        ->groupBy(DB::raw('YEAR(end_date)'), DB::raw('MONTH(end_date)'))
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) {
            $monthName = date('M', mktime(0, 0, 0, $item->month, 1));
            return [
                'period' => $monthName . ' ' . $item->year,
                'expiring_count' => (int)$item->expiring_count
            ];
        });

        // Agreement Status Breakdown
        $agreementStatusData = Agreement::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => (int)$item->count
                ];
            });

        // Monthly Agreement Expiry Trend (Last 6 months + Next 3 months)
        $monthlyExpiryTrend = Agreement::select(
            DB::raw('YEAR(end_date) as year'),
            DB::raw('MONTH(end_date) as month'),
            DB::raw('SUM(CASE WHEN end_date < CURDATE() THEN 1 ELSE 0 END) as expired'),
            DB::raw('SUM(CASE WHEN end_date >= CURDATE() AND end_date <= DATE_ADD(CURDATE(), INTERVAL 3 MONTH) THEN 1 ELSE 0 END) as expiring')
        )
        ->whereYear('end_date', '>=', now()->subMonths(6)->year)
        ->whereYear('end_date', '<=', now()->addMonths(3)->year)
        ->groupBy(DB::raw('YEAR(end_date)'), DB::raw('MONTH(end_date)'))
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get()
        ->map(function ($item) {
            $monthName = date('M', mktime(0, 0, 0, $item->month, 1));
            return [
                'period' => $monthName . ' ' . $item->year,
                'expired' => (int)$item->expired,
                'expiring' => (int)$item->expiring
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
            'expiringThisMonth',
            'monthlyCollection',
            'buildingCollection',
            'collectionTrend',
            'expiredAgreementsData',
            'expiringAgreementsData',
            'agreementStatusData',
            'monthlyExpiryTrend'
        ));
    }
}
