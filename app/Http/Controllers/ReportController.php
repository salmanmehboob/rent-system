<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Building;
use App\Models\RoomShop;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReportController extends Controller
{
    // -------------------------------
    public function getCustomerReports(Request $request)
    {
        if ($request->ajax()) {
            $invoices = Invoice::with('customer')->select('id', 'customer_id', 'created_at', 'rent_amount', 'paid', '');

            return DataTables::of($invoices)
                ->addColumn('customer_name', fn($invoice) => $invoice->customer->name ?? 'N/A')
                ->addColumn('month', fn($invoice) => Carbon::parse($invoice->created_at)->format('F'))
                ->addColumn('rent', fn($invoice) => $invoice->rent_amount)
                ->addColumn('paid_amount', fn($invoice) => $invoice->paid)
                ->addColumn('dues', fn($invoice) => $invoice->remaining)
                ->addColumn('payment_date', fn($invoice) => $invoice->created_at)
                ->make(true);
        }

        $customers = Customer::orderBy('name')->get();
        $title = "Customer Reports";
        return view('reports.customers.index', compact('title', 'customers'));
    }


    // -------------------------------
    public function getBuildingReports(Request $request)
    {
        if ($request->ajax()) {
            $buildings = Building::with('rooms')
                ->select('id', 'name');

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
            $invoices = Invoice::with(['customer', 'roomShop.building'])
                ->where('dues', '>', 0);

            return DataTables::of($invoices)
                ->addColumn('building', fn($i) => $i->roomShop->building->name ?? 'N/A')
                ->addColumn('customer', fn($i) => $i->customer->name ?? 'N/A')
                ->addColumn('property', fn($i) => $i->roomShop->type . ' - ' . $i->roomShop->no)
                ->addColumn('total_dues', fn($i) => $i->dues)
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
