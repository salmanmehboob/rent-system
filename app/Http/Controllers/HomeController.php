<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\RoomShop;
use App\Models\Customer;
use App\Models\Invoice;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $buildingsCount = Building::count();
        $roomhopsCount = RoomShop::count();
        $customersCount = Customer::count();
        $invoicesCount = Invoice::count();

        return view('home', compact('buildingsCount', 'roomhopsCount', 'customersCount', 'invoicesCount'));
    }
}
