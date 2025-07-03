<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Building;

class ExcelExportController extends Controller
{
    public function index(Request $request)
    {
        $buildings = Building::orderBy('name')->get();

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $currentYear = now()->year;
        $startYear = $currentYear - 20;

        if ($request->ajax()) {
            if (!$request->has('building_id')) {
                return response()->json(['error' => 'Missing building_id'], 400);
            }

            $customers = Customer::where('building_id', $request->building_id)->get();

            $selectedBuildings = Building::find($request->building_id);


            foreach ($customers as $customer) {
                $room = $customer->rooms()->first(); // rooms() is a custom method, not relation

                $data[] = [
                    'customer_name' => $customer->name,
                    'roomshop_name' => $room?->no ?? '-',
                    'rent' => $room?->agreements()?->where('status', 'active')->first()?->monthly_rent ?? '-',
                ];
            }


            return response()->json([
                'building' => [
                    'name' => $selectedBuildings->name,
                    'address' => $selectedBuildings->address,
                    'contact_person' => $selectedBuildings->contact_person,
                    'phone' => $selectedBuildings->contact,
                ],
                'customers' => $data,
            ]);
        }

        return view('excel.index', compact('buildings', 'months', 'currentYear', 'startYear'));
    }


}
