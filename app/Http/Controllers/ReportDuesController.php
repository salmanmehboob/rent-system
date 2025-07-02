<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\RoomShop;

use Yajra\DataTables\Facades\DataTables;

class ReportDuesController extends Controller
{
    public function index(Request $request)
    {
        $title = "Dues Reports";
        $buildings = Building::orderBy('name', 'asc')->get();

        if ($request->ajax()) {
            // Build the query from the Invoice model
            $query = Invoice::with(['building', 'customer', 'roomShop']);

            // Filter by building if provided
            if ($request->building_id) {
                $query->where('building_id', $request->building_id);
            }

            // Filter by room/shop if provided
            if ($request->room_shop_id) {
                $query->where('room_shop_id', $request->room_shop_id);
            }

            // Only show invoices with remaining dues
            $query->where('remaining', '>', 0);

            return DataTables::of($query)
                ->addColumn('building', function ($row) {
                    return $row->building->name ?? 'NA';
                })
                ->addColumn('properties', function ($row) {
                    if ($row->roomShop) {
                        return   $row->roomShop->no;
                    }
                    return 'NA';
                })
                ->addColumn('customer', function ($row) {
                    return $row->customer->name ?? 'NA';
                })
                ->addColumn('total_dues', function ($row) {
                    return number_format($row->remaining, 2);
                })
                ->rawColumns(['building', 'properties', 'customer', 'total_dues'])
                ->make(true);
        }

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


    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validate();
        
        }
    }

}
