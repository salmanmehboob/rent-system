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
use App\Models\Transaction;

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
        $dues = Invoice::whereIn('status', ['Unpaid', 'Partially Paid'])->sum('remaining');
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

        // Get months from January to current month of this year
        $months = collect(range(1, now()->month))->map(function ($month) {
            return [
                'year' => now()->year,
                'month' => date('F', mktime(0, 0, 0, $month, 1)),
            ];
        });

        // Paid per month from transactions
        $paidPerMonth = Transaction::select('year', 'month', DB::raw('SUM(paid) as total_paid'))
            ->whereIn(DB::raw("CONCAT(year, '-', month)"), $months->map(fn($m) => $m['year'].'-'.$m['month']))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderByRaw("FIELD(month, '".implode("','", $months->pluck('month')->unique()->toArray())."')")
            ->get()
            ->keyBy(fn($row) => $row->year.'-'.$row->month);

         // Pending per month from invoices
        $pendingPerMonth = Invoice::select('year', 'month', DB::raw('SUM(remaining) as total_remaining'))
            ->whereIn('status', ['Unpaid', 'Partially Paid'])
            ->whereIn(DB::raw("CONCAT(year, '-', month)"), $months->map(fn($m) => $m['year'].'-'.$m['month']))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderByRaw("FIELD(month, '".implode("','", $months->pluck('month')->unique()->toArray())."')")
            ->get()
            ->keyBy(fn($row) => $row->year.'-'.$row->month);

        // Combine for dashboard
        $monthlyCollection = $months->map(function ($m) use ($paidPerMonth, $pendingPerMonth) {
            $key = $m['year'].'-'.$m['month'];
            $monthName = $m['month'];
            $year = $m['year'];
            $paid = (float)($paidPerMonth[$key]->total_paid ?? 0);
            $remaining = (float)($pendingPerMonth[$key]->total_remaining ?? 0);
            return [
                'period' => $monthName . ' ' . $year,
                'paid' => $paid,
                'remaining' => $remaining,
                'total' => $paid + $remaining,
                'count' => null // You can add invoice count if needed
            ];
        });

        // dd($monthlyCollection);
        // Building-wise collection data
        $buildingCollection = Invoice::with('customer.agreements.roomShops.building')
            ->select(
                'customer_id',
                DB::raw('SUM(paid) as total_paid'),
                DB::raw("SUM(CASE WHEN status IN ('Unpaid', 'Partially Paid') THEN remaining ELSE 0 END) as total_remaining")
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
            // Sum paid for all invoices (or you can restrict to status = "Paid" if you want)
            DB::raw('SUM(paid) as collection'),
            // Sum remaining only for unpaid invoices
            DB::raw("SUM(CASE WHEN status IN ('Unpaid', 'Partially Paid') THEN remaining ELSE 0 END) as dues")
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

        // dd($topCustomers);

        // Agreement status breakdown for chart
        $agreementStatusData = Agreement::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($row) {
                return [
                    'status' => ucfirst($row->status),
                    'count' => $row->count
                ];
            });

        // Monthly Expiry Trend Data (for current year, Jan to current month)
        $expiryMonths = collect(range(1, now()->month))->map(function ($month) {
            return [
                'year' => now()->year,
                'month' => $month,
                'period' => date('M', mktime(0, 0, 0, $month, 1)) . ' ' . now()->year
            ];
        });
        $monthlyExpiryTrend = $expiryMonths->map(function ($m) {
            $expired = Agreement::whereYear('end_date', $m['year'])
                ->whereMonth('end_date', $m['month'])
                ->where('end_date', '<', now()->toDateString())
                ->count();
            $expiring = Agreement::whereYear('end_date', $m['year'])
                ->whereMonth('end_date', $m['month'])
                ->where('end_date', '>=', now()->toDateString())
                ->count();
            return [
                'period' => $m['period'],
                'expired' => $expired,
                'expiring' => $expiring
            ];
        });

        // Expired Agreements Data (for current year, Jan to current month)
        $expiredAgreementsData = $expiryMonths->map(function ($m) {
            $expired_count = Agreement::whereYear('end_date', $m['year'])
                ->whereMonth('end_date', $m['month'])
                ->where('end_date', '<', now()->toDateString())
                ->count();
            return [
                'period' => $m['period'],
                'expired_count' => $expired_count
            ];
        });

        // Expiring Agreements Data (for current year, Jan to current month)
        $expiringAgreementsData = $expiryMonths->map(function ($m) {
            $expiring_count = Agreement::whereYear('end_date', $m['year'])
                ->whereMonth('end_date', $m['month'])
                ->where('end_date', '>=', now()->toDateString())
                ->count();
            return [
                'period' => $m['period'],
                'expiring_count' => $expiring_count
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
            'topCustomers',
            'expiredAgreements',
            'expiringThisMonth',
            'monthlyCollection',
            'buildingCollection',
            'collectionTrend',
            'agreementStatusData',
            'monthlyExpiryTrend',
            'expiredAgreementsData',
            'expiringAgreementsData'
        ));
    }
}
