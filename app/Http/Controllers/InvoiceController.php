<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Building;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Agreement;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $title = "Invoices";
        $buildings = Building::orderBy('name', 'asc')->get();

        if ($request->ajax()) {
            $invoices = Invoice::with(['building', 'customer', 'agreement', 'transactions'])->get();
            return DataTables()->of($invoices)
                ->addColumn('building', function ($invoice) {
                    return $invoice->building->name ?? 'N/A';
                })
                ->addColumn('customer', function ($invoice) {
                    return $invoice->customer->name ?? 'N/A';
                })
                ->addColumn('status', function ($invoice) {
                    if ($invoice->status === 'Paid') {
                        $statusText = 'Paid';
                        $badgeClass = 'badge-success';
                    } elseif ($invoice->status === 'Unpaid') {
                        $statusText = 'Unpaid';
                        $badgeClass = 'badge-danger';
                    } else {
                        $statusText = 'Partially Paid';
                        $badgeClass = 'badge-warning';
                    }

                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('actions', function ($invoice) {
                    $buttons = '<div class="d-flex">';

                    // Show Print button only if remaining > 0
                    if ($invoice->remaining > 0) {
                        $buttons .= '
                            <a href="' . route('print', $invoice->id) . '"
                            target="_blank"
                            class="btn btn-secondary shadow btn-sm sharp mx-2"
                            title="Print Invoice"><i class="fa fa-print"></i></a>

                            <a href="#" 
                            class="btn btn-primary shadow btn-sm sharp me-1 payNowBtn"
                            data-url="' . route('invoices.update', $invoice->id) . '"
                            data-id="' . $invoice->id . '"
                            data-name="' . $invoice->customer->name . '"
                            data-month="' . $invoice->month . '"
                            data-year="' . $invoice->year . '"
                            data-rent_amount="' . $invoice->rent_amount . '"
                            data-paid="' . $invoice->paid . '"
                            data-remaining="' . $invoice->remaining . '"
                            >
                            <i class="fas fa-credit-card"></i>
                            </a>
                        ';
                    }

                    // Delete button (always shown)
                    $buttons .= '
                        <a href="javascript:void(0)"
                        data-url="' . route('invoices.destroy', $invoice->id) . '"
                        data-label="delete"
                        data-id="' . $invoice->id . '"
                        data-table="invoicesTable"
                        class="btn btn-danger shadow btn-sm sharp delete-record"
                        style="margin-left:0.5rem;"
                        title="Delete Record"><i class="fa fa-trash"></i></a>
                    ';

                    // Transaction History button (always shown)
                    $buttons .= '
                        <a href="javascript:void(0)"
                        data-url="' . route('invoices.transactions', $invoice->id) . '"
                        class="btn btn-primary shadow btn-sm sharp mx-2 transactionHistoryBtn"
                        data-id="' . $invoice->id . '"
                        data-name="' . $invoice->customer->name . '">
                        <i class="fa fa-history"></i></a>
                    ';

                    $buttons .= '</div>';

                    return $buttons;
                })

            ->rawColumns(['status', 'actions'])
            ->make(true);
        }

        return view('invoices.index', compact('title', 'buildings'));
    }

    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validate([
                'building_id' => 'required|exists:buildings,id',
                'customer_id' => 'required|exists:customers,id',
                'month' => 'required|string',
                'year' => 'required|integer',
                'rent_amount' => 'required|numeric',
                'dues' => 'required|numeric',
                'paid' => 'nullable|numeric',
                'total' => 'required|numeric',
                'remaining' => 'required|string',
                'status' => 'required|in:Unpaid,Paid,Partially Paid',
            ]);

            try {
                DB::beginTransaction();

                $alreadyExists = Invoice::where('customer_id', $validatedData['customer_id'])
                    ->where('month', $validatedData['month'])
                    ->where('year', $validatedData['year'])
                    ->exists();

                if ($alreadyExists) {
                    return response()->json([
                        'errors' => [
                            'global' => [
                                'Receipt for this customer in ' . $validatedData['month'] . ' ' . $validatedData['year'] . ' already exists.'
                            ]
                        ]
                    ], 422);
                }

                $agreement = Customer::find($validatedData['customer_id'])?->agreements->first();

                if (!$agreement) {
                    return response()->json(['error' => 'No active agreement found for this customer.'], 422);
                }

                $validatedData['agreement_id'] = $agreement->id;
                $validatedData['remaining'] = $validatedData['total'];
                // dd($validatedData);
                 $invoice = Invoice::create($validatedData);

                DB::commit();

                return response()->json([
                    'success' => 'Receipt created successfully.',
                    'data' => $invoice
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



    public function combine(Request $request)
    {
        $validatedData = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'month' => 'required|string',
            'year' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $customers = Customer::with(['agreements' => function($query) use ($validatedData) {
                $monthNumber = \DateTime::createFromFormat('F', $validatedData['month'])->format('n');

                $query->where('status', 'Active')
                    ->whereDate('start_date', '<=', Carbon::createFromDate(
                        $validatedData['year'], 
                        $monthNumber, 
                        1
                    )->endOfMonth());
            }, 'agreements.roomShops'])
            ->where('building_id', $validatedData['building_id'])
            ->whereHas('agreements', function($query) use ($validatedData) {
                $monthNumber = \DateTime::createFromFormat('F', $validatedData['month'])->format('n');

                $query->where('status', 'Active')
                    ->whereDate('start_date', '<=', Carbon::createFromDate(
                        $validatedData['year'], 
                        $monthNumber, 
                        1
                    )->endOfMonth());
            })
            ->get();

            $insertedCount = 0;
            $skippedCount = 0;

            if ($customers->isEmpty()) {
                DB::commit();
                return redirect()->back()->with('error', "No active agreements found for {$validatedData['month']} {$validatedData['year']}");
            }

            foreach ($customers as $customer) {
                $agreement = $customer->agreements->first();
                if (!$agreement) {
                    $skippedCount++;
                    continue;
                }

               
                $existingInvoice = Invoice::where('customer_id', $customer->id)
                    ->where('month', $validatedData['month'])
                    ->where('year', $validatedData['year'])
                    ->first();

                if ($existingInvoice) {
                    $skippedCount++;
                    continue;
                }

                $roomCount = $agreement->roomShops->count();
                $monthlyRent = $agreement->monthly_rent;
                $rentAmount = $monthlyRent * $roomCount;

                $lastInvoice = Invoice::where('customer_id', $customer->id)
                    ->where('is_active', true)
                    ->orderByDesc('id')
                    ->first();

                $previousDues = $lastInvoice ? $lastInvoice->remaining : 0;
                $subTotal = $rentAmount + $previousDues;

                Invoice::create([
                    'building_id'     => $validatedData['building_id'],
                    'customer_id'     => $customer->id,
                    'month'           => $validatedData['month'],
                    'year'            => $validatedData['year'],
                    'rent_amount'     => $rentAmount,
                    'dues'            => $previousDues,
                    'remaining'       => $previousDues,
                    'total'           => $subTotal,
                    'status'          => 'Unpaid',
                    'is_active'       => true,
                    'current_dues'    => $subTotal,
                ]);

                $insertedCount++;
            }

            DB::commit();

                
            if ($insertedCount === 0) {
                return redirect()->back()->with('custom_error', 'Not enough data to generate bills.');
            }

            return redirect()->route('bills')->with('custom_success'," {$insertedCount} bills generated successfully. please check Invoices.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('custom_error', 'Failed to generate invoices: ' . $e->getMessage());
        }
    }





    public function update(Request $request, $id)
    {
            $validatedData = $request->validate([
                'paid' => 'required|numeric',
                'note' => 'required|string',
            ]);

            try {
                DB::beginTransaction();

                $invoice = Invoice::find($id);

                // Subtract the amount from dues
                $paidAmount = $validatedData['paid'];
                $remaining = $invoice->remaining - $paidAmount;
                $remaining = $remaining < 0 ? 0 : $remaining;

             
                 // Determine new status
                if ($remaining == 0) {
                    $status = 'Paid';
                } elseif ($remaining < $invoice->dues) {
                    $status = 'Partially Paid';
                } else {
                    $status = 'Unpaid';
                }

                // Update invoice
                $invoice->remaining = $remaining;
                $invoice->status = $status;
                $invoice->paid += $paidAmount;
                $invoice->save();


                 $transactionData = [
                'invoice_id' => $invoice->id,
                'paid' =>  $validatedData['paid'],
                'year' =>$invoice->year,
                'month' => $invoice->month,
                'dues' =>  $remaining,
                'note' => $validatedData['note'],
            ];
                 
                 // insert the data into transactions table
                Transaction::create($transactionData);

                DB::commit();

              return redirect()->route('invoices.index')->with('success','Invoice Paid Successfully');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e->getMessage());
                return redirect()->route('invoices.index')->with('error',$e->getMessage());

               
            }

     }


    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();
            return response()->json(['success' => 'Invoice deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete invoice.', 
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getByBuilding(Request $request)
    {
        $buildingId = $request->building_id;
        $customerId = $request->customer_id;

        $query = Customer::with(['agreements.roomShops', 'invoices'])
            ->where(function($q) use ($buildingId, $customerId) {
                if ($buildingId) {
                    $q->where('building_id', $buildingId);
                }

                if ($customerId) {
                    $q->orWhere('id', $customerId);
                }
            });

        $customers = $query->get()->map(function ($customer) {
            $agreement = $customer->agreements->first();

            $roomCount = $agreement && $agreement->roomShops 
                ? $agreement->roomShops->count() 
                : 0;

            $monthlyRent = $agreement->monthly_rent ?? 0;
            $totalRent = $monthlyRent * $roomCount;
            
            $totalDues = $customer->invoices->sum('remaining');
            $totalPaid = $customer->invoices->sum('paid');
            $remaining = $customer->invoices->sum('remaining');
 
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'rent_amount' => $totalRent,
                'dues' => $totalDues,
                'paid' => $totalPaid,
                'remaining' => $remaining,
                
            ];
        })->values();

        return response()->json($customers);
    }


    public function getTransactions($id)
    {
        $invoice = Invoice::with('transactions')->find($id);

        return response()->json([
            'transactions' => $invoice->transactions
        ]);
    }

    public function show() 
    {
        $title = "Generate All Bills";
        $buildings = Building::orderBy('name', 'asc')->get();

        return view('all-bills.index', compact('title', 'buildings'));
    }

}