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
                    } elseif ($invoice->status === 'Partially Paid') {
                        $statusText = 'Partially Paid';
                        $badgeClass = 'badge-warning';
                    } else {
                        $statusText = 'Dues Adjusted';
                        $badgeClass = 'badge-info';
                    }

                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })

                 ->addColumn('type', function ($invoice) {
                    if ($invoice->type === 'Current') {
                        $statusText = 'Current';
                        $badgeClass = 'badge-success';
                    } else {
                        $statusText = 'Previous';
                        $badgeClass = 'badge-danger';
                    }

                    return '<span class="badge ' . $badgeClass . '">' . $statusText . '</span>';
                })
                ->addColumn('actions', function ($invoice) {
                    $buttons = '<div class="d-flex">';

                       $buttons .= '
                            <a href="' . route('print', $invoice->id) . '"
                            target="_blank"
                            class="btn btn-secondary shadow btn-sm sharp mx-2"
                            title="Print Invoice"><i class="fa fa-print"></i></a>
                            ';
                    // Show Print button only if remaining > 0
                    if ($invoice->type === 'Current') {
                        if($invoice->remaining > 0) {
                            $buttons .= '

                                <a href="#" 
                                class="btn btn-primary shadow btn-sm sharp me-1 payNowBtn"
                                data-url="' . route('invoices.update', $invoice->id) . '"
                                data-id="' . $invoice->id . '"
                                data-name="' . $invoice->customer->name . '"
                                data-month="' . $invoice->month . '"
                                data-year="' . $invoice->year . '"
                                data-rent_amount="' . $invoice->rent_amount . '"
                                data-paid="' . $invoice->paid . '"
                                data-dues="' . $invoice->dues . '"
                                data-remaining="' . $invoice->remaining . '">
                                <i class="fas fa-credit-card"></i>
                                </a>
                            ';

                        }
                       


                        $buttons .='<a href="javascript:void(0)"
                            data-url="' . route('invoices.transactions', $invoice->id) . '"
                            class="btn btn-primary shadow btn-sm sharp mx-2 transactionHistoryBtn"
                            data-id="' . $invoice->id . '"
                            data-name="' . $invoice->customer->name . '">
                            <i class="fa fa-history"></i></a>
                        ';
                    }

                    $buttons .= '</div>';

                    return $buttons;
                })

            ->rawColumns(['status', 'type', 'actions'])
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
                'year' => 'required|string',
                'rent_amount' => 'required|numeric',
                'dues' => 'required|numeric',
                'total' => 'required|numeric',
                'status' => 'required|in:Unpaid,Paid,Partially Paid,Dues Adjusted',
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

                $customer = Customer::find($validatedData['customer_id']);

                $agreement = Customer::find($validatedData['customer_id'])?->agreements()
                ->where('status', 'active')
                ->latest()
                ->first();

                if (!$agreement) {
                    return response()->json([
                        'errors' => [
                            'global' => ['No active agreement found for '. $customer->name .'.']
                        ]
                    ], 422);

                }

                $validatedData['agreement_id'] = $agreement->id;
                $validatedData['remaining'] = $validatedData['dues'] + $validatedData['rent_amount'];
                $validatedData['paid'] = $validatedData['paid'] ?? 0;


                // Prepare the new invoice date
                $newInvoiceDate = Carbon::createFromFormat('d-F-Y H:i:s', '01-' . $validatedData['month'] . '-' . $validatedData['year'] . ' 00:00:00');

                $existingInvoices = Invoice::where('customer_id', $validatedData['customer_id'])->get();

                if ($existingInvoices->count()) {
                    $latestInvoice = $existingInvoices->map(function ($invoice) {
                        return [
                            'model' => $invoice,
                            'date' => Carbon::createFromFormat('d-F-Y H:i:s', '01-' . $invoice->month . '-' . $invoice->year . ' 00:00:00'),
                        ];
                    })->sortByDesc('date')->first();

                    // If the new one is newer
                    if ($newInvoiceDate->gt($latestInvoice['date'])) {
                        $validatedData['type'] = 'Current';

                        // Update all existing invoices of this customer to 'Previous'
                        Invoice::where('customer_id', $validatedData['customer_id'])
                            ->where('type', 'Current')
                            ->update(['type' => 'Previous', 'status' => 'Dues Adjusted']);
                    } else {
                        $validatedData['type'] = 'Previous';
                    }
                } else {
                    // First invoice for this customer
                    $validatedData['type'] = 'Current';
                }


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
            'year' => 'required|string',
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

                // Check if invoice already exists for this customer/month/year
                $existingInvoice = Invoice::where('customer_id', $customer->id)
                    ->where('month', $validatedData['month'])
                    ->where('year', $validatedData['year'])
                    ->first();

                if ($existingInvoice) {
                    $skippedCount++;
                    continue;
                }
                

                // Calculate rent amount
                $roomCount = $agreement->roomShops->count();
                $monthlyRent = $agreement->monthly_rent;
                $rentAmount = $monthlyRent * $roomCount;

                // Get previous dues (remaining from last invoice)
                $lastInvoice = Invoice::where('customer_id', $customer->id)
                    ->where('is_active', true)
                    ->orderByDesc('id')
                    ->first();

                $previousDues = $lastInvoice ? $lastInvoice->remaining : 0;
                $total = $rentAmount + $previousDues;

                // check existing
                $newInvoiceDate = Carbon::createFromFormat('d-F-Y H:i:s', '01-' . $validatedData['month'] . '-' . $validatedData['year'] . ' 00:00:00');

                $existingInvoices = Invoice::where('customer_id', $customer->id)->get();

                if ($existingInvoices->count()) {
                    $latestInvoice = $existingInvoices->map(function ($invoice) {
                        return [
                            'model' => $invoice,
                            'date' => Carbon::createFromFormat('d-F-Y H:i:s', '01-' . $invoice->month . '-' . $invoice->year . ' 00:00:00'),
                        ];
                    })->sortByDesc('date')->first();

                    if ($newInvoiceDate->gt($latestInvoice['date'])) {
                        $type = 'Current';

                        Invoice::where('customer_id', $customer->id)
                            ->where('type', 'Current')
                            ->update(['type' => 'Previous', 'status' => 'Dues Adjusted']);
                    } else {
                        $type = 'Previous';
                    }
                } else {
                    $type = 'Current';
                }


                // Create the invoice with proper values
                Invoice::create([
                    'building_id'     => $validatedData['building_id'],
                    'customer_id'     => $customer->id,
                    'agreement_id'    => $agreement->id,
                    'month'           => $validatedData['month'],
                    'year'            => $validatedData['year'],
                    'rent_amount'     => $rentAmount,
                    'dues'            => $previousDues,
                    'paid'            => 0, // Default to 0 for new invoices
                    'total'           => $total,
                    'remaining'       => $total, // Initially remaining equals total
                    'type'            => $type,
                    'status'          => $type === 'Previous' ? 'Dues Adjusted' : 'Unpaid',
                    'is_active'       => true,
                ]);

                $insertedCount++;
            }

            DB::commit();

            if ($insertedCount === 0) {
                return redirect()->back()->with('custom_error', 'No new bills generated. All customers already have invoices for this period.');
            }

           $message = "{$insertedCount} bills generated successfully.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} customers skipped (already had invoices).";
            }

            return redirect()->route('bills')->with('custom_success', $message);


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

                $rentDues = $invoice->rent_amount + $invoice->dues;
             
                 // Determine new status
                if ($remaining == 0) {
                    $status = 'Paid';
                } elseif ($remaining < $rentDues) {
                    $status = 'Partially Paid';
                } else {
                    $status = 'Unpaid';
                }

                // Update invoice
                $invoice->remaining = $remaining;
                $invoice->status = $status;
                $invoice->paid += $paidAmount;
                // If fully paid, reset dues
                if ($remaining == 0) {
                    $invoice->dues = 0;
                }
                $invoice->save();


                 $transactionData = [
                'invoice_id' => $invoice->id,
                'paid' =>  $validatedData['paid'],
                'year' =>$invoice->year,
                'month' => $invoice->month,
                'remaining' =>  $remaining,
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
            
            // $latestInvoice = $customer->invoices()->latest()->first();
            // $totalDues = $latestInvoice?->dues ?? 0;
            $latestInvoice = $customer->invoices()->latest()->first();
            $dues = $latestInvoice?->remaining ?? 0;
 
            return [
                'id' => $customer->id,
                'name' => $customer->name,
                'rent_amount' => $totalRent,
                'dues' => $dues,
            ];
        })->values();

        return response()->json($customers);
    }


    public function getTransactions($id)
    {
        $invoice = Invoice::with('customer')->find($id);

        // Get all transactions for the same customer, across all invoices
        $transactions = Transaction::with('invoice.customer')
            ->whereHas('invoice', function ($query) use ($invoice) {
               $query->where('customer_id', $invoice->customer_id);
            })
            ->get();

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    public function show() 
    {
        $title = "Generate All Bills";
        $buildings = Building::orderBy('name', 'asc')->get();

        return view('all-bills.index', compact('title', 'buildings'));
    }

}