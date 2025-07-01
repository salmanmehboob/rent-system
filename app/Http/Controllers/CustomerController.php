<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Building;
use App\Models\RoomShop;
use App\Models\Witness;
use App\Models\Agreement;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


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
                ->addColumn('property', function ($customer) {
                    $agreement = optional($customer->agreements)->first();

                    if ($agreement && $agreement->roomShops->isNotEmpty()) {
                        return $agreement->roomShops->map(function ($shop) {
                            return $shop->type . '-' . $shop->no;
                        })->implode(', ');
                    }

                    return 'N/A';
                })


                ->addColumn('start_date', function ($customer) {
                    return $customer->agreements->first()->start_date ?? 'N/A';
                })
                ->addColumn('end_date', function ($customer) {
                    return $customer->agreements->first()->end_date ?? 'N/A';
                })
                ->addColumn('duration', function ($customer) {
                    $agreement = $customer->agreements->first();
                    return $agreement && $agreement->duration 
                        ? $agreement->duration . ' months' 
                        : 'N/A';
                })
                ->addColumn('monthly_rent', function ($customer) {
                    $agreement = $customer->agreements->first();
                    return $agreement && $agreement->monthly_rent 
                        ? 'Rs. ' . $agreement->monthly_rent 
                        : 'N/A';
                })
                // Witness section (show only first witness)
                // ->addColumn('witness_name', function ($customer) {
                //     return $customer->witnesses->first()->name ?? 'N/A';
                // })
                // ->addColumn('witness_mobile_no', function ($customer) {
                //     return $customer->witnesses->first()->mobile_no ?? 'N/A';
                // })
                // ->addColumn('witness_cnic', function ($customer) {
                //     return $customer->witnesses->first()->cnic ?? 'N/A';
                // })
                // ->addColumn('witness_address', function ($customer) {
                //     return $customer->witnesses->first()->address ?? 'N/A';
                // })
                // Actions
               ->addColumn('actions', function ($customer) {
                    $agreement = $customer->agreements->first();
                    $roomNos = $roomNos = $agreement && $agreement->roomShops ? $agreement->roomShops->pluck('no')->toArray() : [];
                    $type = $type = $agreement && $agreement->roomShops ? $agreement->roomShops->pluck('type')->toArray() : [];
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
                                data-type="' . implode(', ', $type) . '"
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
                'name' => [
                    'required',
                    'string',
                    Rule::unique('customers')->where('building_id', request('building_id')),
                ],
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


                $customer = Customer::find($id);

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
                    $oldRoomShopIds = $agreement->roomShops()->pluck('room_shop_id')->toArray();
 
                    $agreement->roomShops()->detach();

                    RoomShop::whereIn('id', $oldRoomShopIds)->update([
                        'customer_id' => null,
                        'availability' => 1,
                    ]);
                    // dd($request->all());

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
            $customer->delete();

            RoomShop::where('customer_id', $customer->id)->update([
                'customer_id' => null,
                'availability' => 1,
            ]);

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
                if (!empty($selectedRoomShopId)) {
                    $query->orWhereIn('id', (array) $selectedRoomShopId);
                }
            });

        return response()->json($query->get()->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->type . ' - ' . $room->no
            ];
        }));
    }

    public function showAgreement()
    {
        $title = "Set New Agreement";
        // $customers = Customer::orderBy('name', 'asc')->get();
        // Get customers whose agreements have expired
        $expiredCustomerIds = Agreement::where('end_date', '<', now())
            ->pluck('customer_id')
            ->unique();

            // dd($expiredCustomerIds);
            $customers = Customer::whereIn('id', $expiredCustomerIds)->orderBy('name', 'asc')->get();
        $rooms = RoomShop::orderBy('no', 'asc')->get();

        return view('customers.agreements', compact('title', 'customers', 'rooms'));
    }




    public function setAgreement(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'room_shop_id' => 'required|array|min:1',
            'room_shop_id.*' => 'exists:room_shops,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'duration' => 'required|string',
            'monthly_rent' => 'required|string',
        ]);

       try {
            DB::beginTransaction();

                $customer = Customer::find($validatedData['customer_id']);

                //  Check if the customer already has an active agreement
                if ($customer->agreements()->where('status', 'active')->exists()) {
                    DB::rollBack();
                    return redirect()->back()->with('custom_error', $customer->name. ' already has an active agreement.');
                }
                // check for overlapping dates
                $hasOverlap = $customer->agreements()
                    ->where(function ($query) use ($validatedData) {
                        $query->where('start_date', '<=', $validatedData['end_date'])
                            ->where('end_date', '>=', $validatedData['start_date']);
                    })->exists();

                if ($hasOverlap) {
                    return redirect()->back()->with('custom_error', 'change the date, becuase this is already in agreement which is inactive');
                }

                // Agreement create
                $agreement = $customer->agreements()->create([
                    'duration' => $validatedData['duration'],
                    'monthly_rent' => $validatedData['monthly_rent'],
                    'start_date' => $validatedData['start_date'],
                    'end_date' => $validatedData['end_date'],
                    'status' => 'active',
                ]);
                // Attach room shops to agreement and update availability
                $agreement->roomShops()->attach($request->room_shop_id);

                RoomShop::whereIn('id', $request->room_shop_id)->update([
                    'customer_id' => $customer->id,
                    'availability' => 0,
                ]);    

            DB::commit();
                return redirect()->route('agreement.show')->with('custom_success', 'New agreement added to '. $customer->name .'.');
       } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['error' => $e->getMessage()], 500);
       }
    }

}

