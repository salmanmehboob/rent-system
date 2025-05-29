@extends('layouts.app')
@section('title', $title)
@push('css')
@endpush
@php
    $months = [
        'January', 'Februry', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
    ];

    $currentYear = now()->year;
    $startYear = $currentYear - 20;

@endphp

@section('content')
    <div class="row">
        <div class="col-md-10" style="margin-left: 5rem;">
            <div class="card">
                <div class="card-header">
                    <h2>{{ $title }} Form</h2>
                </div>
                <div class="card-body">
                    <form class="ajax-form" data-table="customersTable" action="{{ route('reports.customers') }}">
                        @csrf
                      <div class="row">
                        <div class="col-md-5" style="padding-left: 5rem;">
                            <div class="form-group mb-2">
                                    <Label for="customers">Customer <span class="text-danger">*</span></Label>
                                    <select name="customer_id" id="customer_id" class="form-control single-select-placehoder select2">
                                        <option value="">select a customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                            </div>

                            <div class="form-group mb-2">
                                    <Label for="start_month">From Month <span class="text-danger">*</span></Label>
                                    <select name="start_month" id="start_month" class="form-control single-select-placeholder select2">
                                        <option value="" >select month</option>
                                        @foreach ($months as $month)
                                        <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                            </div>

                            <div class="form-group mb-2">
                                    <label for="start_year">From Year <span class="text-danger">*</span></label>
                                    <select name="start_year" id="start_year" class="form-control single-select-paceholder select2">
                                        <option value="">select year</option>
                                        @for ($year = $currentYear; $year >= $startYear; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                            </div>
                        </div>
                        <div class="col-md-5" style="padding-left: 5rem;">
                            <div class="form-group mb-2" style="margin-top: 4.3rem;">
                                    <Label for="end_month">To Month <span class="text-danger">*</span></Label>
                                    <select name="end_month" id="end_month" class="form-control single-select-placeholder select2">
                                        <option value="" >select month</option>
                                        @foreach ($months as $month)
                                        <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                            </div>

                            <div class="form-group mb-2">
                                <label for="end_year">To Year <span class="text-danger">*</span></label>
                                <select name="end_year" id="end_year" class="form-control single-select-paceholder select2">
                                    <option value="">select year</option>
                                    @for ($year = $currentYear; $year >= $startYear; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                      </div>

                      <button type="submit" id="submitBtn" class="btn btn-primary float-end submit-btn">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<hr>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>{{ $title }} List</h2>
                </div>
                <div class="card-body">
                    <div class="table-resposive">
                        <table class="table table-bordered" id="customersTable" width="100%" cellspacing="0">
                           <thead>
                            <th>Id</th>
                            <th>Customer</th>
                            <th>Month</th>
                            <th>Rent</th>
                            <th>Paid Amount</th>
                            <th>Dues</th>
                            <th>Payment Date</th>
                           </thead>
                           <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')



    <script>
       $(document).ready(function() {

            $('#customersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('reports.customers') }}",
                dom: 'Bfrtip', // 👈 enables button bar
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                columns: [
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'month',
                        name: 'month'
                    },
                    {
                        data: 'rent',
                        name: 'rent'
                    },
                    {
                        data: 'paid_amount',
                        name: 'paid_amount'
                    },
                    {
                        data: 'dues',
                        name: 'dues'
                    },
                    {
                        data: 'payment_date',
                        name: 'payment_date'
                    },
                ]
            });
       });
    </script>
@endpush