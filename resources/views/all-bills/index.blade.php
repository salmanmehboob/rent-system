@extends('layouts.app')
@section('title', $title)

@section('content')
    @php
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $currentYear = now()->year;
        $startYear = $currentYear - 20;

    @endphp
    @if(session('custom_success'))
        <div class="alert alert-success">
            {{ session('custom_success') }}
        </div>
    @endif


    @if(session('custom_error'))
        <div class="alert alert-danger">
            {{ session('custom_error') }}
        </div>
    @endif
    <div class="row">

        <div class="col-md-8 mx-5">
            <div class="card">
                <div class="card-header" style="display: flex;">
                    <h2>{{ $title }}</h2>
                    <a href="{{ route('invoices.index') }}"
                       style="margin-left: 15rem; margin-top: 0.5rem; font-size:1.2rem">Go to Invoices</a>
                </div>
                <div class="card body">
                    <form action="{{ route('combine-bills') }}" method="POST" class="p-5">
                        @csrf
                        <div class="form-group mb-2">
                            <label for="building_id"> Building <span class="text-danger">*</span></label>
                            <select name="building_id" id="building_id"
                                    class="form-control single-select-placeholder select2">
                                <option value="" disabled selected> Select a Building</option>
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                            <span id="building_idError" class="text-danger"></span>
                        </div>

                        <div class="form-group mb-2">
                            <label for="month">Month <span class="text-danger">*</span></label>
                            <select name="month" id="month" class="form-control select2">
                                <option value="">select month</option>
                                @foreach ($months as $month)
                                    <option value="{{ $month }}">{{ $month }}</option>
                                @endforeach
                            </select>
                            <span id="monthError" class="text-danger"></span>
                        </div>

                        <div class="form-group mb-2">
                            <label for="year">Year <span class="text-danger">*</span></label>
                            <select name="year" id="year" class="form-control select2">
                                <option value="">select Year</option>
                                @for ($year=$currentYear; $year>= $startYear; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                            <span id="yearError" class="text-danger"></span>
                        </div>

                        <input type="submit"  class="d-none d-sm-inline-block btn btn-lg btn-primary shadow-sm mt-4" value="Generate">

{{--                        <button type="submit" id="submitBtn"--}}
{{--                                class="d-none d-sm-inline-block btn btn-lg btn-primary shadow-sm mt-4">Generate All--}}
{{--                            Receipts--}}
{{--                        </button>--}}
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('js')

    <script>
        setTimeout(function () {
            $('.alert').fadeOut('slow');
        }, 4000);
    </script>

@endpush
