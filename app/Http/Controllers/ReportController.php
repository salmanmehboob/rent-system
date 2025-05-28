<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Agreement;
use App\Models\Building;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // 1. Customer Reports
    public function customerReports()
    {
        $title = "Customers";
        $customers = Customer::with('agreements')->get();

        return view('reports.customers.index', compact('customers', 'title'));
    }

    // 2. Dues Reports
    public function duesReports()
    {
        $title = "Dues";
        $customersWithDues = Customer::whereHas('transations', function ($query) {
            $query->whereStatus('Unpaid'); // Assuming 'is_paid' exists
        })->with(['transations' => function ($query) {
            $query->where('paid', false);
        }])->get();

        return view('reports.dues.index', compact('customersWithDues', 'title'));
    }

    // 3. Building Reports
    public function buildingReports()
    {
        $title = "Buildings";
        $buildings = Building::with('rooms')->get(); // Assuming 'rooms' relation exists

        return view('reports.buildings.index', compact('buildings', 'title'));
    }
}
