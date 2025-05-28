@extends('layouts.app')
@section('title', $title)

@section('content')
    <h2>{{ $title }}</h2>
   <table class="table table-striped">
    <thead>
        <tr>
            <th>Customer</th>
            <th>Email</th>
            <th>Pending Agreements</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customersWithDues as $customer)
        <tr>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->email }}</td>
            <td>
                @foreach($customer->agreements as $agreement)
                    <div>Agreement ID: {{ $agreement->id }} | Due: {{ $agreement->due_amount ?? 'N/A' }}</div>
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection