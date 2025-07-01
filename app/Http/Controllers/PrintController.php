<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function printInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);

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
            ->with('customer') // adjust depending on your relationship
            ->get();

        return view('prints.print-latest', compact('invoices', 'latest'));
    }

}