<?php

namespace App\Http\Controllers;
use App\Models\Transaction;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function printInvoice($id)
    {
        $transaction = Transaction::findorFail($id);

        return view('invoices.index', compact('transaction'));
    }
}