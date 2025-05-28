<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RoomShop;
use App\Models\Building;


class RoomShopController extends Controller
{
      /**
     * Display a listing of the rooms and shops.
     */

    public function index(Request $request)
    {
        $title = 'Rooms/Shops';
        $buildings = Building::orderBy('name', 'asc')->get();

        if ($request->ajax()) {

            $roomshops = RoomShop::with('building')->get();

                    return DataTables()->of($roomshops)
                        ->addColumn('building', function ($roomshop) {
                            return $roomshop->building ? $roomshop->building->name : 'N/A';
                        })
                        ->addColumn('availability', function ($roomshop) {
                            $badgeClass = $roomshop->availability ? 'badge-success' : 'badge-danger';
                            $statusText = $roomshop->availability ? 'Available' : 'Unavailable';
                            return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                        })

                        ->addColumn('actions', function ($roomshop) {
                            return '
                            <div class="d-flex">
                                <a id="editBtn" data-url="' . route('roomshops.update', $roomshop->id) . '"
                                data-id="' . $roomshop->id . '"
                                data-building="' . $roomshop->building_id . '"
                                data-type="' . $roomshop->type . '"
                                data-no="' . $roomshop->no . '"
                                data-availability="' . $roomshop->availability . '"
                                href="javascript:void(0)"
                                class="btn btn-primary shadow btn-sm sharp me-1"><i class="fas fa-pencil-alt"></i></a>

                                <a href="javascript:void(0)"
                                data-url="' . route('roomshops.destroy', $roomshop->id) . '"
                                data-label="delete"
                                data-id="' . $roomshop->id . '"
                                data-table="roomshopsTable"
                                class="btn btn-danger shadow btn-sm sharp delete-record"
                                style="margin-left:0.5rem;"
                                title="Delete Record"><i class="fa fa-trash"></i></a>
                            </div>
                        ';
                        })
                        ->rawColumns(['actions', 'availability']) 
                        ->make(true);

                     }

        return view('roomshops.index', compact('title', 'buildings'));
    }




      /**
     * Store a newly created item in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {

            $validatedData = $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'customer_id' => 'nullable|exists:customers,id',
                'type' => 'required|string',
                'no' => 'required|string',
                'availability' => 'required|boolean',
              
            ]);

            try {
                // Start a database transaction
                DB::beginTransaction(); 

              // Create the room/shop
                 $roomshop = RoomShop::create($validatedData);

                // Commit the transaction
                DB::commit();


                return response()->json(['success' =>  'room/shop created successfully.', 'data' => $roomshop], 201);
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                return response()->json(['success' =>  'Failed to create room/shop.', 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
    }



       /**
     * update an existing room or shop.
     */
    public function update(Request $request,$id)
    {

        if ($request->ajax()) {

            $validated = $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'customer_id' => 'nullable|exists:customers,id',
                'type' => 'required|string',
                'no' => 'required|string',
                'availability' => 'required|boolean',
              
            ]);

            try {
                // Start a database transaction
                DB::beginTransaction(); 

                // Create the room/shop 
                 $roomshop = RoomShop::findOrFail($id);
                 $roomshop->update($validated);

                // Commit the transaction
                DB::commit();


                return response()->json(['success' =>  'room/shop updated successfully.', 'data' => $roomshop], 201);
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                return response()->json(['success' =>  'Failed to update room/shop.', 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
    }


    public function destroy($id)
    {
    
        try {
            $roomshop=RoomShop::findOrFail($id);
            $roomshop->delete();
            return response()->json(['success' => 'RoomShop deleted successfully.']);
        } catch (\Exception $e) {
           return response()->json(['error' => 'Failed to delete RoomShop.', 'message' => $e->getMessage()], 500);

        }
    }

   


}
