<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class BuildingController extends Controller
{
    /**
     * Display all buildings.
     */
    public function index(Request $request)
    {
        $title = 'Buildings';

        if ($request->ajax()) {
            $buildings = Building::all();
            return DataTables::of($buildings)
                ->addColumn('actions', function ($building) {
                    return '
                        <div class="d-flex align-items-center">
                            <a id="editBtn" data-url="' . route('buildings.update', $building->id) . '"
                               data-id="' . $building->id . '"
                               data-name="' . $building->name . '"
                               data-address="' . $building->address . '"
                               data-contact="' . $building->contact . '"
                               data-contact_person="' . $building->contact_person . '"
                               href="javascript:void(0)"
                               class="btn btn-primary shadow btn-sm sharp">
                               <i class="fas fa-pencil-alt fa-sm"></i></a>

                            <a href="javascript:void(0)"
                               data-url="' . route('buildings.destroy', $building->id) . '"
                               data-id="' . $building->id . '"
                               data-table="buildingsTable"
                               class="btn btn-danger shadow btn-sm sharp delete-record"
                               style="margin-left: 1.5rem" title="Delete Record">
                               <i class="fa fa-trash fa-sm"></i></a>
                        </div>
                    ';
                })
               
                ->rawColumns(['actions'])
                ->make(true);
        }

        return view('buildings.index', compact('title'));
    }

    /**
     * Store a new building.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:buildings,name',
                'address' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            DB::beginTransaction();

            Building::create([
                'name' => $request->name,
                'address' => $request->address,
                'contact' => $request->contact,
                'contact_person' => $request->contact_person,

            ]);

            DB::commit();

            return response()->json(['success' => 'Building added successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Building Store Error: ' . $e->getMessage());

            return response()->json(['error' => 'Something went wrong. Please try again.'], 500);
        }
    }

    /**
     * Update an existing building.
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:buildings,name,' . $id,
                'address' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'contact_person' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $building = Building::findOrFail($id);
            $building->update([
                'name' => $request->name,
                'address' => $request->address,
                'contact' => $request->contact,
                'contact_person' => $request->contact_person,

            ]);

            return response()->json(['success' => 'Building updated successfully.']);
        } catch (\Exception $e) {
            Log::error('Building Update Error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to update building. Please try again.'], 500);
        }
    }

    /**
     * Delete a building.
     */
    public function destroy($id)
    {
        try {
            $building = Building::findOrFail($id);
            $building->delete();

            return response()->json(['success' => 'Building deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Building Delete Error: ' . $e->getMessage());

            return response()->json(['error' => 'Failed to delete building.'], 500);
        }
    }
}
