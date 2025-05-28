@extends('layouts.app')
@section('title', $title)

@section('content')
    <h2>{{ $title }}</h2>
   
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Agreements</th>
        </tr>
    </thead>
    <tbody>
        @foreach($customers as $customer)
        <tr>
            <td>{{ $customer->name }}</td>
            <td>{{ $customer->email }}</td>
            <td>{{ $customer->phone }}</td>
            <td>
                @foreach($customer->agreements as $agreement)
                    <div>Agreement #{{ $agreement->id }} - {{ $agreement->is_paid ? 'Paid' : 'Unpaid' }}</div>
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection