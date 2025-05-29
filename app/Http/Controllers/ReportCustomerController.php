<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\CustomerReport; 
use App\Models\Invoice;
use Yajra\DataTables\Facades\DataTables;


class ReportCustomerController extends Controller
{
    public function index(Request $request)
    {
        $title = "Customer Reports";
        $customers = Customer::orderBy('name', 'asc')->get();

        if ($request->ajax()) {
                $reports = CustomerReport::with('customer')->select('customer_reports.*');

               return DataTables::of($reports)
                ->addColumn('customer_name', function($report) {
                    return $report->customer->name ?? 'N/A';
                })
               ->make(true);

            }

        return view('reports.customers.index', compact('title', 'customers'));
    }




    public function store(Request $request)
    {
        if ($request->ajax()) {
            $validatedData = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'start_month' => 'required|string',
                'end_month' => 'required|string',
                'start_year' => 'required|integer',
                'end_year' => 'required|integer',
            ]);

            try {
                DB::beginTransaction();

                $customerId = $validatedData['customer_id'];
                $startMonth = $validatedData['start_month'];
                $endMonth = $validatedData['end_month'];
                $startYear = $validatedData['start_year'];
                $endYear = $validatedData['end_year'];

                $monthToNumber = [
                    'January' => 1, 'Februry' => 2, 'March' => 3,
                    'April' => 4, 'May' => 5, 'June' => 6,
                    'July' => 7, 'August' => 8, 'September' => 9,
                    'October' => 10, 'November' => 11, 'December' => 12,
                ];

                $startMonthNum = $monthToNumber[$startMonth];
                $endMonthNum = $monthToNumber[$endMonth];

                $startDate = date("Y-m-d", strtotime("$startYear-$startMonthNum-01"));
                $endDate = date("Y-m-t", strtotime("$endYear-$endMonthNum-01"));

                // ✅ Now use Invoice instead of Transaction
                $invoices = Invoice::where('customer_id', $customerId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();

                foreach ($invoices as $invoice) {
                    CustomerReport::create([
                        'customer_id'   => $invoice->customer_id,
                        'month'         => date('F', strtotime($invoice->invoice_date)),
                        'rent'          => $invoice->rent_amount,
                        'paid_amount'   => $invoice->paid,
                        'dues'          => $invoice->remaining,
                        'payment_date'  => $invoice->created_at,
                    ]);
                }

                DB::commit();
                return response()->json(['success' => 'Report created successfully'], 200);

            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Invalid request.'], 400);
    }


}
