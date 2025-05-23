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
<div class="row">
    <div class="col-md-10 mx-5">
        <div class="card">
            <div class="card-header">
                <h4>{{ $title}} Form</h4>
            </div>
            <div class="card-body">
                <form class="ajax-form" id="transactionForm" data-table="transactionsTable" action="{{ route('transactions.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-5 mx-5">
                            <div class="form-group mb-2">
                                <label>Building  <span class="text-danger">*</span></label>
                                <select name="building_id" id="building_id" class=" form-control single-select-placehoder select2">
                                    <option value="" disabled selected> Select a Building </option>
                                    @foreach ($buildings as $building)
                                        <option value="{{ $building->id }}">{{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                         
                            <div class="form-group mb-2">
                                <label for="month">Month <span class="text-danger">*</span></label>
                                <select name="month" id="month" class="form-control select2">
                                    <option value="">select month</option>
                                    @foreach ($months as $month)
                                        <option value="{{ $month }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>

                             <div class="form-group mb-2">
                                <label for="year">Year <span class="text-danger">*</span></label>
                                <select name="year" id="year" class="form-control select2">
                                    <option value="" >select Year</option>
                                    @for ($year=$currentYear; $year>= $startYear; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>


                            <div class="form-group mb-2">
                                <label for="date">Previous Dues <span class="text-danger">*</span></label>
                                <input type="text" name="previous_dues" class="form-control" id="previous_dues" placeholder="enter any prvious dues">
                            </div>

                            <div class="form-group mb-2">
                                <label for="date">Payable Amount <span class="text-danger">*</span></label>
                                <input type="text" name="payable_amount" class="form-control" id="payable_amount" placeholder="enter payment to be paid now">
                            </div>

                        </div>

                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label>Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-control select2" id="customer_id">
                                    <!-- This will be filled dynamically with customer based on selected building -->
                                    <option value="">Select Customer</option>
                                </select>
                            </div>


                            <div class="form-group mb-2">
                                <label for="rent_amount">Rent Amount <span class="text-danger">*</span></label>
                                <input type="text" name="rent_amount" class="form-control" id="rent_amount" readonly>
                            </div>

                            <div class="form-group mb-2">
                                <label for="address">Sub Total<span class="text-danger">*</span></label>
                                <input type="text" name="sub_total" class="form-control" id="sub_total" readonly>
                            </div>

                            <div class="form-group mb-2">
                                <label for="address">Current Dues<span class="text-danger">*</span></label>
                                <input type="text" name="current_dues" class="form-control" id="current_dues" readonly>
                            </div>

                            <div class="form-group mb-2">
                                <Label for="status">Payment Status <span class="text-danger">*</span></Label>
                                <select name="status" class="form-control  select2">
                                    <option value="">select status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Upaid">Unpaid</option>
                                    <option value="Partially Paid">Partially Paid</option>
                                </select>
                            </div>
                        </div>
                   </div>

                  
                    <button type="submit" id="submitBtn" class="btn btn-primary submit-btn px-4">Save</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary float-end me-2 d-none">Cancel Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-12 my-4">
        <div class="card">
            <div class="card-header">
                <h4>{{$title}} List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="transactionsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Building</th>
                                <th>Customer</th>
                                <th>Month</th>
                                <th>Rent Amount</th>
                                <th>Previous Dues</th>
                                <th>Sub Total</th>
                                <th>Payable Amount</th>
                                <th>Current Dues</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


</div>

@endsection
@push('js')
<script>
            // Set customerId globally (e.g., for edit form, from data attribute)
    let customerId = $('#customer_id').data('selected-id') || null;

    $('#building_id').on('change', function() {
        const buildingId = $(this).val();
        if (buildingId) {
            loadDependentDropdown('/customer-by-building', buildingId, 'customer_id', customerId, 'Select Customer', 'building_id');
        } else {
            $('#customer_id').html('<option value="">Select Customer</option>').trigger('change');
            $('#rent_amount').val('');
        }
    });

    function loadDependentDropdown(url, parentId, childSelectId, selectedId = null, defaultText = 'Select', parentKey) {
        if (!parentKey) return;

        const data = { [parentKey]: parentId };
        if (selectedId) {
            data.customer_id = selectedId;
        }

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function(data) {
                const $select = $(`#${childSelectId}`);
                let options = `<option value="">${defaultText}</option>`;
              

                data.forEach(item => {
                 
                    options += `<option data-rent="${item.rent_amount}" value="${item.id}">${item.name}</option>`;
                });

                $select.html(options);
                
                // Refresh Select2 if it exists
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.trigger('change.select2');
                }
            },
            error: function() {
                alert('Error loading options');
            }
        });
    }


    $('#customer_id').on('change', function () {
        const selectedOption = $(this).find('option:selected'); // get selected <option>
        const rent = selectedOption.data('rent');               // get data-rent from it
        $('#rent_amount').val(rent ||0);
    });



      //caculate subtotal on rent amount and previous dues
    $('#rent_amount, #previous_dues').on('change', function() {
        const rent = parseFloat($('#rent_amount').val()) || 0;
        const dues = parseFloat($('#previous_dues').val()) || 0;
        const subtotal = rent + dues;
        $('#sub_total').val(subtotal);
    });

    //calculate current dues on payable amount
     $('#sub_total, #payable_amount').on('change', function() {
        const total = parseFloat($('#sub_total').val()) || 0;
        const payable = parseFloat($('#payable_amount').val()) || 0;
        const current = total - payable;
        $('#current_dues').val(current);
    });



    //Ready fucntion start
    $(document).ready(function() {
       
        $('#transactionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('transactions.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'building', name: 'building' },
                { data: 'customer', name: 'customer' },
                { data: 'month', name: 'month' },
                { data: 'rent_amount', name: 'rent_amount' },
                { data: 'previous_dues', name: 'previous_dues' },
                { data: 'sub_total', name: 'sub_total' },
                { data: 'payable_amount', name: 'payable_amount' },
                { data: 'current_dues', name: 'current_dues' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });
         
        // Edit button 
        $(document).on('click', '#editBtn', function(e) {
            e.preventDefault();
            
            const $form = $('#transactionForm');
            $form.attr('action', $(this).data('url'));
            $form.append('<input type="hidden" name="_method" value="PUT">');

            // Populate form
            $form.find('input[name="month"]').val($(this).data('month'));
            $form.find('input[name="rent_amount"]').val($(this).data('rent_amount'));
            $form.find('input[name="previous_dues"]').val($(this).data('previous_dues'));
            $form.find('input[name="sub_total"]').val($(this).data('sub_total'));
            $form.find('input[name="payable_amount"]').val($(this).data('payable_amount'));
            $form.find('input[name="current_dues"]').val($(this).data('current_dues'));
            $form.find('select[name="status"]').val($(this).data('status')).trigger('change');

            const buildingId = $(this).data('building');
            const customerId = $(this).data('customer');
            $form.find('select[name="building_id"]').val(buildingId).trigger('change');
            // Load customer dropdown and select value
            setTimeout(() => {
                loadDependentDropdown('/customer-by-building', buildingId, 'customer_id', customerId, 'Select customer', 'building_id', customerId );// this will trigger edit behavior
            }, 300);

            
            $('#submitBtn').text('Update');
            $('#cancelBtn').removeClass('d-none');
        });


         // Handle Cancel Update
        $(document).on('click', '#cancelBtn', function() {
            const $form = $('form.ajax-form');
            $form.attr('action', "{{ route('customers.store') }}");
            $form.find('input[name="_method"]').remove();
            $form[0].reset();
            $form.find('select').val(null).trigger('change');
            $('#submitBtn').text('Save');
            $(this).addClass('d-none');
            
           
        });
    });
</script>
@endpush
