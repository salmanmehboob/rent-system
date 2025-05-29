<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\BuildingReport;
use Yajra\DataTables\Facades\DataTables;

class ReportBuildingController extends Controller
{
    public function index(Request $request)
    {
        $title = "Building Reports";
        $buildings = Building::orderBy('name', 'asc')->get();

        if ($request->ajax()) {
            $reports = BuildingReport::with('building')->select('building_reports.*');
            return DataTables::of($reports)
            ->addColumn('building_name', function($report){
                return $report->building->name ?? 'NA';
            })
            ->make(true);
        }

        return view('reports.buildings.index', compact('title', 'buildings'));
    }



    public function store(Request $request)
    {
        if ($request->ajax()) {

            $validatedData = $request->validate([
                'building_id' => 'required|exists:buildings,id',
            ]);

            $buildingId = $validatedData['building_id'];
            $building = Building::find($buildingId);

            $totalRooms = $building->rooms()->where('type', 'room')->count();
            $totalShops = $building->rooms()->where('type', 'shop')->count();

            $availableRooms = $building->rooms()->where('type', 'room')->where('availability', 1)->count();
            $availableShops = $building->rooms()->where('type', 'shop')->where('availability', 1)->count();

            $totalRented = $building->rooms()->where('availability',0)->count();

            try {
                  //  start database transaction
                 DB::beginTransaction();

                    BuildingReport::create([
                        'building_id' => $buildingId,
                        'total_rooms' => $totalRooms,
                        'total_shops' => $totalShops,
                        'available_rooms' => $availableRooms,
                        'available_shops' => $availableShops,
                        'total_rented_roomshops' => $totalRented,
                    ]);
                  // commint the transaction
                 BD::commit();
                return response()->json(['success' => 'Building Report created successfully'], 200);
            } catch (\Exception $e) {
                //roll back the transaction
                DB::rollback();
                return response()->json(['error' => 'Failed to create Building Report ', 'message' => $e->getMessge()], 500);
            }
        }
        return response()->json(['success' => false, 'message' => 'invalide request'], 400);
    }
}
