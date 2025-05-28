@extends('layouts.app')
@section('title', $title)

@section('content')
    <h2>{{ $title }}</h2>
   @foreach($buildings as $building)
    <h4>{{ $building->name }}</h4>
    <ul>
        @foreach($building->rooms as $room)
            <li>
                {{ $room->name }} -
                {{ $room->is_occupied ? 'Occupied' : 'Available' }}
            </li>
        @endforeach
    </ul>
   @endforeach

@endsection