<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            ->whereBetween('created_at', [$overlapStart, $overlapEnd]);

        return DataTables::of($invoices)
            ->addColumn('customer_name', fn($row) => $row->customer->name ?? 'N/A')

            // 👇 Custom "month" column based on agreement range overlap
            ->addColumn('month', function ($row) use ($overlapStart, $overlapEnd) {
                $monthsCount = ceil($overlapStart->diffInMonths($overlapEnd)) + 1;
                $monthLabel = $overlapStart->format('M Y') . ' – ' . $overlapEnd->format('M Y');
                return "{$monthsCount} ({$monthLabel})";
            })

            ->addColumn('rent', fn($row) => $row->rent_amount)
            ->addColumn('paid_amount', fn($row) => $row->paid)
            ->addColumn('dues', fn($row) => $row->remaining)
            ->addColumn('payment_date', fn($row) => Carbon::parse($row->created_at)->format('F d, Y'))
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
        $invoices = Invoice::select('invoices.*') // Explicitly select invoice columns
            ->with(['agreement.roomShops.building', 'agreement.customer'])
            ->where('invoices.remaining', '>', 0); // Specify table for column


            if (empty($request->building_id) && empty($request->room_shop_id)) {
                return response()->json([
                    'data' => [],
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0
                ]);
            }


        if ($request->building_id) {
            $invoices->whereHas('agreement.roomShops', function ($query) use ($request) {
                $query->where('room_shops.building_id', $request->building_id); // Specify table
            });
        }

        if ($request->room_shop_id) {
            $invoices->whereHas('agreement.roomShops', function ($query) use ($request) {
                $query->where('room_shops.id', $request->room_shop_id); // Specify table
            });
        }

        return DataTables::of($invoices)
            ->addColumn('building', function ($invoice) {
                return optional($invoice->agreement->roomShops->first()->building)->name ?? 'N/A';
            })
            ->addColumn('customer', function ($invoice) {
                return $invoice->agreement->customer->name ?? 'N/A';
            })
            ->addColumn('property', function ($invoice) {
                $roomShop = $invoice->agreement->roomShops->first();
                return $roomShop ? $roomShop->type . ' - ' . $roomShop->no : 'N/A';
            })
            ->addColumn('total_dues', function($invoice) {
                return $invoice->remaining;
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
        $rooms = RoomShop::where('building_id', $buildingId)
            ->get(['id', 'type', 'no']);

        // Format each room/shop for display
        return response()->json($rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->type . ' - ' . $room->no
            ];
        }));
    }
}
