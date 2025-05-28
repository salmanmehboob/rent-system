<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Building;
use App\Models\RoomShop;
use App\Models\Witness;
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
                    'agreements' => function ($query) {
                        $query->where('status', 'active')->latest()->with('roomShops'); // <- ADD THIS
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
                    $agreement = optional($customer->agreements)->first();

                    return $agreement && $agreement->roomShops->isNotEmpty()
                        ? implode(', ', $agreement->roomShops->pluck('no')->toArray())
                        : 'N/A';
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
                    $roomNos = $roomNos = $agreement && $agreement->roomShops ? $agreement->roomShops->pluck('no')->toArray() : [];
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
                                data-room_shop_id=\'' . json_encode($agreement?->roomShops->pluck('id')->toArray() ?? []) . '\'
                                data-room_shop_no="' . implode(', ', $roomNos) . '"
                                data-name="' . e($customer->name) . '"
                                data-mobile_no="' . e($customer->mobile_no) . '"
                                data-cnic="' . e($customer->cnic) . '"
                                data-address="' . e($customer->address) . '"
                                data-status="' . e($customer->status) . '"
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
                'room_shop_id' => 'required|array|min:1',
                'room_shop_id.*' => 'exists:room_shops,id',
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

                // Create Agreement and get the instance
                $agreement = $customer->agreements()->create([
                    'duration' => $request->duration,
                    'monthly_rent' => $request->monthly_rent,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => 'active',
                ]);

                // Attach room shops to agreement and update availability
                $agreement->roomShops()->attach($request->room_shop_id);

                RoomShop::whereIn('id', $request->room_shop_id)->update([
                    'customer_id' => $customer->id,
                    'availability' => 0,
                ]);

                // Create or find witnesses, collect their IDs
                $witnessIds = [];
                foreach ($request->witnesses as $witnessData) {
                    $witness = Witness::updateOrCreate(
                        ['id' => $witnessData['id'] ?? null],
                        [
                            'name' => $witnessData['name'],
                            'mobile_no' => $witnessData['mobile_no'],
                            'cnic' => $witnessData['cnic'],
                            'address' => $witnessData['address'],
                        ]
                    );
                    $witnessIds[] = $witness->id;
                }

                // Attach witnesses to customer and agreement (assuming many-to-many)
                $customer->witnesses()->sync($witnessIds);
                $agreement->witnesses()->sync($witnessIds);

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
                'room_shop_id' => 'required|array|min:1',
                'room_shop_id.*' => 'exists:room_shops,id',
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

                // Update customer
                $customer->update($request->only(['building_id', 'name', 'mobile_no', 'cnic', 'address', 'status']));

                // Agreement update or create
                $agreement = $customer->agreements()->first();

                $agreementData = [
                    'duration' => $request->duration,
                    'monthly_rent' => $request->monthly_rent,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => 'active',
                ];

                if ($agreement) {
                    $agreement->update($agreementData);

                    // Detach old room shops
                    $oldRoomShopIds = $agreement->roomShops()->pluck('id')->toArray();
                    $agreement->roomShops()->detach();

                    RoomShop::whereIn('id', $oldRoomShopIds)->update([
                        'customer_id' => null,
                        'availability' => 1,
                    ]);
                } else {
                    $agreement = $customer->agreements()->create($agreementData);
                }

                // Attach new room shops
                $agreement->roomShops()->sync($request->room_shop_id);

                RoomShop::whereIn('id', $request->room_shop_id)->update([
                    'customer_id' => $customer->id,
                    'availability' => 0,
                ]);

                // Sync witnesses
                $witnessIds = [];
                foreach ($request->witnesses as $witnessData) {
                    $witness = \App\Models\Witness::updateOrCreate(
                        ['id' => $witnessData['id'] ?? null],
                        [
                            'name' => $witnessData['name'],
                            'mobile_no' => $witnessData['mobile_no'],
                            'cnic' => $witnessData['cnic'],
                            'address' => $witnessData['address'],
                        ]
                    );
                    $witnessIds[] = $witness->id;
                }

                $customer->witnesses()->sync($witnessIds);
                $agreement->witnesses()->sync($witnessIds);

                DB::commit();

                return response()->json(['success' => 'Customer updated successfully.'], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
            return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
        }

       
    }




  public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);

            // // Check if customer has related agreements
            // if ($customer->agreements()->exists()) {
            //     return response()->json([
            //         'errors' => [ // <-- wrap global inside 'errors'
            //             'global' => [
            //                 'Customer cannot be deleted. Please delete related agreements and transactions first.'
            //             ]
            //         ]
            //     ], 400);

            // }

            $customer->delete();

            return response()->json(['success' => 'Customer deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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

