<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Building;
use App\Models\RoomShop;
use App\Models\Agreement;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReportController extends Controller
{
    // -------------------------------
    public function getCustomerReports(Request $request)
    {
        if ($request->ajax()) {
            // Ensure customer is selected
            if (!$request->customer_id) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }

            // Fetch customer's agreement
            $agreement = Agreement::where('customer_id', $request->customer_id)->first();

            if (!$agreement) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }

            // Get selected date range
            $startDate = Carbon::createFromDate($request->start_year, $request->start_month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($request->end_year, $request->end_month, 1)->endOfMonth();

            // Agreement range
            $agreementStart = Carbon::parse($agreement->start_date)->startOfMonth();
            $agreementEnd = Carbon::parse($agreement->end_date)->endOfMonth();

            // Overlapping period
            $overlapStart = $startDate->greaterThan($agreementStart) ? $startDate : $agreementStart;
            $overlapEnd = $endDate->lessThan($agreementEnd) ? $endDate : $agreementEnd;

            // If no overlap, return empty
            if ($overlapStart->gt($overlapEnd)) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }
  

          


            // Fetch invoices in overlapping range
            $invoices = Invoice::with('customer')
                ->where('customer_id', $request->customer_id)
                ->whereBetween('invoice_date', [$overlapStart, $overlapEnd]);

                // for the footer of the table 
                $invoiceClone = clone $invoices;
                $summary = $invoiceClone->get()->reduce(function ($carry, $invoice) {
                    $carry['rent'] += $invoice->rent_amount;
                    $carry['paid'] += $invoice->paid;
                    $carry['dues'] = $carry['rent'] - $carry['paid'];
                    return $carry;
                }, ['rent' => 0, 'paid' => 0, 'dues' => 0]);

            return DataTables::of($invoices)
                ->addColumn('customer_name', fn($row) => $row->customer->name ?? 'N/A')

                //  Custom "month" column based on agreement range overlap
                // ->addColumn('month', function ($row) use ($overlapStart, $overlapEnd) {
                //     $monthsCount = ceil($overlapStart->diffInMonths($overlapEnd)) + 1;
                //     $monthLabel = $overlapStart->format('M Y') . ' – ' . $overlapEnd->format('M Y');
                //     return "{$monthsCount} ({$monthLabel})";
                // })
                ->addColumn('month', function($invoice) {
                    return  $invoice->month. '-' .$invoice->year;
                })
                ->addColumn('rent', fn($row) => $row->rent_amount)
                ->addColumn('paid_amount', fn($row) => $row->paid)
                ->addColumn('dues', fn($row) => $row->remaining)
                ->addColumn('payment_date', fn($row) => Carbon::parse($row->created_at)->format('F d, Y'))
                ->with('summary', $summary) // Pass totals to frontend
            ->make(true);
        }

        $customers = Customer::orderBy('name')->get();
        return view('reports.customers.index', [
            'title' => 'Customer Reports',
            'customers' => $customers,
        ]);
    }



    // -------------------------------
    public function getBuildingReports(Request $request)
    {
        if ($request->ajax()) {
            $buildings = Building::with('rooms')
                ->select('id', 'name');

            // 🧠 Return empty result if no building selected
            if (empty($request->building_id)) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }

            // 🧠 Filter by selected building
            if ($request->filled('building_id')) {
                $buildings->where('id', $request->building_id);
            }

            return DataTables::of($buildings)
                ->addColumn('building_name', fn($b) => $b->name)
                ->addColumn('total_rooms', fn($b) => $b->rooms->where('type', 'room')->count())
                ->addColumn('total_shops', fn($b) => $b->rooms->where('type', 'shop')->count())
                ->addColumn('available_rooms', fn($b) => $b->rooms->where('type', 'room')->where('availability', 1)->count())
                ->addColumn('available_shops', fn($b) => $b->rooms->where('type', 'shop')->where('availability', 1)->count())
                ->addColumn('total_rented_roomshops', fn($b) => $b->rooms->where('availability', 0)->count())
                ->make(true);
        }

        $buildings = Building::orderBy('name')->get();
        $title = "Building Reports";
        return view('reports.buildings.index', compact('title', 'buildings'));
    }


    // -------------------------------

    public function getDuesReports(Request $request)
    {
        if ($request->ajax()) {

            if (empty($request->building_id) && empty($request->customer_id)) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }

            $invoices = Invoice::select('invoices.*')
                ->with(['agreement.roomShops.building', 'agreement.customer'])
                ->where('invoices.remaining', '>', 0)
                ->whereRaw('invoices.id = (
                    SELECT MAX(id) FROM invoices as i2
                    WHERE i2.agreement_id = invoices.agreement_id
                )')
                ->whereHas('agreement.customer', function ($query) use ($request) {
                    if ($request->building_id) {
                        $query->where('building_id', $request->building_id);
                    }
                    if ($request->customer_id) {
                        $query->where('id', $request->customer_id);
                    }
                })
                ->get()
                ->groupBy(function ($invoice) {
                    return $invoice->agreement->customer->id;
                });

            // NOW use collection mode (not server-side)
            return DataTables::of($invoices->values()) // `->values()` resets keys for DT
                ->addColumn('building', function ($group) {
                    return optional($group->first()->agreement->roomShops->first()->building)->name ?? 'N/A';
                })
                ->addColumn('customer', function ($group) {
                    $customer = $group->first()->agreement->customer;
                    return $customer ? $customer->name . ' (' . $customer->mobile_no . ')' : 'N/A';
                })
                ->addColumn('properties', function ($group) {
                    $rooms = [];
                    foreach ($group as $invoice) {
                        foreach ($invoice->agreement->roomShops as $room) {
                            $rooms[] =   $room->no;
                        }
                    }
                    return implode(', ', array_unique($rooms));
                })
                ->addColumn('total_dues', function ($group) {
                    return $group->sum('remaining');
                })
                ->make(true);
        }

        $buildings = Building::orderBy('name')->get();
        $title = "Dues Reports";
        return view('reports.dues.index', compact('title', 'buildings'));
    }





    public function getByBuilding(Request $request)
    {
        $buildingId = $request->building_id;

        // Get all rooms/shops for the selected building
        $customers = Customer::where('building_id', $buildingId)
            ->get(['id', 'name', 'mobile_no']);

        // Format each room/shop for display
        return response()->json($customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'name' => $customer->name . ' - ' . $customer->mobile_no
            ];
        }));
    }
}
