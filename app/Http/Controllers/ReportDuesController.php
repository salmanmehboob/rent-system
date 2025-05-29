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

        if($request->ajax())
        {
            $reports = ::with('building')->select('building_reports.*');
            return $reports = DataTables::of($reports)
            ->addColumn('building', function($row){
                return $row->building->name ?? 'NA';
            })
            ->addColumn('customer', function ($row) {
                return $row->building->customers()->where('building_id', $request->building->id)->name ?? 'NA';
            })
            ->addColumn('property', function($row) {
                return $row->building->rooms()->where('building_id', $request->building->id)->type && ->no ?? 'NA';
            })
            ->addColumn('total_dues', function($row) {
                return $row->building->invoices()->where('building_id', $request->building->id)->where('customer_id', $request->customer->id)->remianing
            })
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
