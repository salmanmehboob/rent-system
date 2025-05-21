<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Building;
use App\Models\RoomShop;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $title = "Customers";
        $buildings = Building::orderBy('name', 'asc')->get();

        if ($request->ajax()) {
            // Proper eager loading
              $customers = Customer::with([
                    'building',
                    'agreements' => function($query) {
                        $query->where('status', 'active')
                            ->whereHas('room', function($q) {
                                $q->where('availability', 0);
                            });
                    },
                    'witnesses'
                ])->get();
            return DataTables()->of($customers)
                ->addColumn('building_name', function ($customer) {
                    return $customer->building->name ?? 'N/A';
                })
                ->addColumn('status', function ($customer) {
                    $statusText = $customer->status === 'active' ? 'Active' : 'Inactive';
                    $badgeClass = $customer->status === 'active' ? 'badge-success' : 'badge-danger';
                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })
                // Agreement section (get first agreement only)
                ->addColumn('room_shop_no', function ($customer) {
                    $activeAgreement = $customer->agreements->first(function ($agreement) {
                        return $agreement->status === 'active' && 
                            $agreement->room && 
                            $agreement->room->availability == 0;
                    });
                    
                    return $activeAgreement ? $activeAgreement->room->no : 'N/A';
                })
                ->addColumn('start_date', function ($customer) {
                    return $customer->agreements->first()->start_date ?? 'N/A';
                })
                ->addColumn('end_date', function ($customer) {
                    return $customer->agreements->first()->end_date ?? 'N/A';
                })
                ->addColumn('duration', function ($customer) {
                    return $customer->agreements->first()->duration ?? 'N/A';
                })
                ->addColumn('monthly_rent', function ($customer) {
                    return $customer->agreements->first()->monthly_rent ?? 'N/A';
                })
                // Witness section (show only first witness)
                ->addColumn('witness_name', function ($customer) {
                    return $customer->witnesses->first()->name ?? 'N/A';
                })
                ->addColumn('witness_mobile_no', function ($customer) {
                    return $customer->witnesses->first()->mobile_no ?? 'N/A';
                })
                ->addColumn('witness_cnic', function ($customer) {
                    return $customer->witnesses->first()->cnic ?? 'N/A';
                })
                ->addColumn('witness_address', function ($customer) {
                    return $customer->witnesses->first()->address ?? 'N/A';
                })
                // Actions
                ->addColumn('actions', function ($customer) {
                    $agreement = $customer->agreements->first();
                    $room = $agreement?->room;
                    $roomNo = $room?->no ?? '';
                    $roomId = $room?->id ?? '';
                    $startDate = $agreement?->start_date ?? '';
                    $endDate = $agreement?->end_date ?? '';
                    $duration = $agreement?->duration ?? '';
                    $monthlyRent = $agreement?->monthly_rent ?? '';
                    $status = $agreement?->status ?? '';

                    return '
                        <div class="d-flex">
                            <a id="editBtn"
                            data-url="' . route('customers.update', $customer->id) . '"
                            data-id="' . $customer->id . '"
                            data-building="' . $customer->building_id . '"
                            data-room_shop_id="' . $roomId . '"
                            data-name="' . $customer->name . '"
                            data-mobile_no="' . $customer->mobile_no . '"
                            data-cnic="' . $customer->cnic . '"
                            data-address="' . $customer->address . '"
                            data-status="' . $customer->status . '"
                            data-room_shop_no="' . $roomNo . '"
                            data-start_date="' . $startDate . '"
                            data-end_date="' . $endDate . '"
                            data-duration="' . $duration . '"
                            data-monthly_rent="' . $monthlyRent . '"
                            data-witnesses=\'' . json_encode($customer->witnesses) . '\'
                            href="javascript:void(0)"
                            class="btn btn-primary shadow btn-sm sharp me-1"><i class="fas fa-pencil-alt"></i></a>

                            <a href="javascript:void(0)"
                            data-url="' . route('customers.destroy', $customer->id) . '"
                            data-label="delete"
                            data-id="' . $customer->id . '"
                            data-table="customersTable"
                            class="btn btn-danger shadow btn-sm sharp delete-record"
                            style="margin-left:0.5rem;"
                            title="Delete Record"><i class="fa fa-trash"></i></a>
                        </div>
                    ';
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('customers.index', compact('title', 'buildings'));
    }



    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validated = $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'name' => 'required|string|max:255|unique:customers,name',
                'mobile_no' => 'required|string|max:15',
                'cnic' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'required|in:active,inactive',

                // Agreement
                'room_shop_id' => 'required|exists:room_shops,id',
                'duration' => 'required|string',
                'monthly_rent' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',

                // Witnesses
                'witnesses' => 'required|array|min:1',
                'witnesses.*.id' => 'nullable|exists:witnesses,id',
                'witnesses.*.name' => 'required|string',
                'witnesses.*.mobile_no' => 'required|string',
                'witnesses.*.cnic' => 'required|string',
                'witnesses.*.address' => 'required|string',
            ]);

            try {
                DB::beginTransaction();

                // Create Customer
                $customer = Customer::create([
                    'building_id' => $request->building_id,
                    'name' => $request->name,
                    'mobile_no' => $request->mobile_no,
                    'cnic' => $request->cnic,
                    'address' => $request->address,
                    'status' => $request->status,
                ]);

                // Create Agreement
                $customer->agreements()->create([
                    'room_shop_id' => $request->room_shop_id,
                    'duration' => $request->duration,
                    'monthly_rent' => $request->monthly_rent,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                ]);

                // Create Witnesses
                foreach ($request->witnesses as $witness) {
                    $customer->witnesses()->create($witness);
                }

                DB::commit();
                return response()->json(['success' => 'Customer added successfully.'], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false,'error' => $e->getMessage(),], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
    }


    // public function edit(Customer $customer)
    // {
    //     return response()->json($customer);
    // }
        public function update(Request $request, $id)
        {
            if ($request->ajax()) {
                $validated = $request->validate([
                    'building_id' => 'required|exists:buildings,id',
                    'name' => 'required|string|max:255',
                    'mobile_no' => 'required|string|max:15',
                    'cnic' => 'nullable|string|max:20',
                    'address' => 'nullable|string',
                    'status' => 'required|in:active,inactive',

                    // Agreement fields
                    'room_shop_id' => 'required|exists:room_shops,id',
                    'duration' => 'required|string',
                    'monthly_rent' => 'required|string',
                    'start_date' => 'required|date',
                    'end_date' => 'required|date',

                    // Witness fields
                    'witnesses' => 'required|array|min:1',
                    'witnesses.*.id' => 'nullable|exists:witnesses,id',
                    'witnesses.*.name' => 'required|string',
                    'witnesses.*.mobile_no' => 'required|string',
                    'witnesses.*.cnic' => 'required|string',
                    'witnesses.*.address' => 'required|string',
                ]);

                try {
                    DB::beginTransaction();
                            
                    $customer = Customer::findOrFail($id);
                    $customer->update($request->only(['building_id', 'name', 'mobile_no', 'cnic', 'address', 'status']));

                    // Update or create agreement
                    $agreementData = $request->only(['room_shop_id', 'duration', 'monthly_rent', 'start_date', 'end_date']);
                    $agreementData['status'] = 'active'; // Ensure status is active

                    if ($customer->agreements()->exists()) {
                        $agreement = $customer->agreements()->first();
                        $agreement->update($agreementData);
                    } else {
                        $customer->agreements()->create($agreementData);
                    }

                    // Update witnesses
                    $customer->witnesses()->delete();
                    foreach ($request->witnesses as $witness) {
                        $customer->witnesses()->create($witness);
                    }

                    DB::commit();
                    return response()->json(['success' => 'Customer updated successfully.'], 200);

                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }

            return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
        }





    public function destroy($id)
    {
        try {
            $customer=Customer::findorFail($id);
            $customer->delete();
            return response()->json(['success' => 'Customer deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete customer.', 'message' => $e->getMessage()], 500);
        }
    }

    
    public function getByBuilding(Request $request)
    {
        $buildingId = $request->building_id;
        $selectedRoomShopId = $request->selected_room_shop_id;

        $query = RoomShop::where('building_id', $buildingId)
            ->where(function($query) use ($selectedRoomShopId) {
                $query->available(); // Use our new scope
                
                // Include currently selected room if editing
                if ($selectedRoomShopId) {
                    $query->orWhere('id', $selectedRoomShopId);
                }
            });

        return response()->json($query->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->type . ' - ' . $room->no
            ];
        }));
    }



}

