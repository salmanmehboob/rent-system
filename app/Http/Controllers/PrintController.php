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
}