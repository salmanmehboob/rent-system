<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Agreement;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $title = "Recipts";
        $buildings = Building::orderBy('name', 'asc')->get();

        if ($request->ajax()) {
            
            // Proper eager loading
           $transactions = Transaction::with(['building', 'customer', 'agreement'])->get();
            return DataTables()->of($transactions)
                ->addColumn('building', function ($transaction) {
                    return $transaction->building->name ?? 'N/A';
                })
                 ->addColumn('customer', function ($transaction) {
                    return $transaction->customer->name ?? 'N/A';
                })
                ->addColumn('status', function ($transaction) {
                    if ($transaction->status === 'Paid') {
                        $statusText = 'Paid';
                        $badgeClass = 'badge-success';
                    } elseif ($transaction->status === 'Unpaid') {
                        $statusText = 'Unpaid';
                        $badgeClass = 'badge-danger';
                    } else {
                        $statusText = 'Partially Paid';
                        $badgeClass = 'badge-warning';
                    }

                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })

                ->addColumn('rent_amount', function ($transaction) {
                    $monthlyRent = $transaction->agreement?->monthly_rent ?? 'N/A';
                })
              
                // Actions
              ->addColumn('actions', function ($transaction) {
                    $agreement = $transaction->agreement;
                    $monthlyRent = $agreement?->monthly_rent ?? 'N/A';

                    return '
                        <div class="d-flex">
                        
                            <a href="' . route('invoice', $transaction->id) . '"
                            target="_blank"
                            class="btn btn-secondary shadow btn-sm sharp mx-2"
                            title="Print Transaction"><i class="fa fa-print"></i></a>

                            <a id="editBtn"
                            data-url="' . route('transactions.update', $transaction->id) . '"
                            data-id="' . $transaction->id . '"
                            data-building="' . $transaction->building_id . '"
                            data-customer_id="' . $transaction->customer_id. '"
                            data-month="' . $transaction->month . '"
                            data-year="' . $transaction->year . '"
                            data-rent_amount="' . $monthlyRent . '"
                            data-previous_dues="' . $transaction->previous_dues . '"
                            data-sub_total="' . $transaction->sub_total . '"
                            data-payable_amount="' . $transaction->payable_amount . '"
                            data-current_dues="' . $transaction->current_dues . '"
                            data-status="' . $transaction->status . '"
                            href="javascript:void(0)"
                            class="btn btn-primary shadow btn-sm sharp me-1"><i class="fas fa-pencil-alt"></i></a>

                            <a href="javascript:void(0)"
                            data-url="' . route('transactions.destroy', $transaction->id) . '"
                            data-label="delete"
                            data-id="' . $transaction->id . '"
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

        return view('transactions.index', compact('title', 'buildings'));
    }




     /**
     * Store a newly created transaction in storage.
     */
   public function store(Request $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'customer_id' => 'required|exists:customers,id',
                'month' => 'required|string',
                'year' => 'required|integer',
                'rent_amount' => 'required|string',
                'previous_dues' => 'required|string',
                'sub_total' => 'required|string',
                'payable_amount' => 'required|string',
                'current_dues' => 'required|string',
                'status' => 'required|in:Unpaid,Paid,Partially Paid',
            ]);

            try {
                DB::beginTransaction();

                // Prevent duplicate transaction
                $alreadyExists = Transaction::where('customer_id', $validatedData['customer_id'])
                    ->where('month', $validatedData['month'])
                    ->where('year', $validatedData['year'])
                    ->exists();

                if ($alreadyExists) {
                    return response()->json([
                    'errors' => [ // <-- wrap global inside 'errors'
                        'global' => [
                            'Recipt for this customer in ' . $validatedData['month'] . ' ' . $validatedData['year'] . ' already exists.'
                        ]
                    ]
                ], 422);

                }

                $agreement = Customer::find($validatedData['customer_id'])?->activeAgreement;

                if (!$agreement) {
                    return response()->json(['error' => 'No active agreement found for this customer.'], 422);
                }

                $validatedData['agreement_id'] = $agreement->id;

                $transaction = Transaction::create($validatedData);

                DB::commit();

                return response()->json([
                    'success' => 'Transaction created successfully.',
                    'data' => $transaction
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
    }




      /**
     * update an existing transaction .
     */
    public function update(Request $request, $id)
    {

        if ($request->ajax()) {
 

            $validatedData = $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'customer_id' => 'required|exists:customers,id',
                'month' => 'required|string',
                'year' => 'required|integer',
                'rent_amount' => 'required|string',
                'previous_dues' => 'required|string',
                'sub_total' => 'required|string',
                'payable_amount' => 'required|string',
                'current_dues' => 'required|string',
                'status' => 'required|in:Unpaid,Paid,Partially Paid',
              
            ]);

            try {
                // Start a database transaction
                DB::beginTransaction(); 

                $transaction = transaction::findOrFail($id);
               // now update the transaction
                 $transaction->update($validatedData);

                // Commit the transaction
                DB::commit();


                return response()->json(['success' =>  'transaction updated successfully.', 'data' => $transaction], 200);
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                return response()->json(['success' =>  'Failed to update transaction.', 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
    }


   public function destroy($id)
    {
    
        try {
            $transaction=Transaction::findOrFail($id);
            $transaction->delete();
            return response()->json(['success' => 'transaction deleted successfully.']);
        } catch (\Exception $e) {
           return response()->json(['error' => 'Failed to delete transaction.', 'message' => $e->getMessage()], 500);

        }
    }





   
    public function getByBuilding(Request $request)
    {
        $buildingId = $request->building_id;
        $customerId = $request->customer_id;

        // Get agreements that belong to customers in the building
        $agreements = Agreement::with('customer')
            ->whereHas('customer', function ($q) use ($buildingId, $customerId) {
                $q->where('building_id', $buildingId)
                ->where('status', 'active');

                if ($customerId) {
                    $q->orWhere('id', $customerId);
                }
            })
            ->get();

        $data = $agreements->map(function ($agreement) {
            $roomCount = $agreement->room_shop_ids 
                ? count(json_decode($agreement->room_shop_ids, true)) 
                : 0;

            $monthlyRent = $agreement->monthly_rent ?? 0;
            $totalRent = $monthlyRent * $roomCount;

            return [
                'id' => $agreement->customer->id,
                'name' => $agreement->customer->name,
                'rent_amount' => $totalRent,
            ];
        });

        return response()->json($data);
    }


}

