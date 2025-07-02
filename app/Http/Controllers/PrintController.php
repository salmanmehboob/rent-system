<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printInvoice($id)
    {
        $invoice = Invoice::with('customer.building')->where('id', $id)->first();
        // Eager load customer, their rooms/shops, and building details
        
         // Ensure $invoice is not null and is a single model, not a collection
        if (!$invoice) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }

         return view('prints.index', compact('invoice'));
    }


    public function printLatest()
    {
        $latest = Invoice::latest('created_at')->first();

        if (!$latest) {
            return redirect()->back()->with('error', 'No invoices available to print.');
        }

        $invoices = Invoice::where('month', $latest->month)
            ->where('year', $latest->year)
            ->with('customer.building') // adjust depending on your relationship
            ->get();

        return view('prints.print-latest', compact('invoices', 'latest'));
    }

}