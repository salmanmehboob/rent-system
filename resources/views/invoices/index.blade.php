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
                <div id="formError" class="alert alert-danger d-none"></div>
                <form class="ajax-form" id="invoiceForm" data-table="invoicesTable" action="{{ route('invoices.store') }}" method="POST">
                    @csrf
                <div id="formError" class="alert alert-danger d-none"></div>
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
                                <span id="building_idError" class="text-danger"></span>
                            </div>

                            <div class="form-group mb-2">
                                <label>Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-control select2" id="customer_id">
                                    <!-- This will be filled dynamically with customer based on selected building -->
                                    <option value="">Select Customer</option>
                                </select>
                                <span id="customer_idError" class="text-danger"></span>
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
                                    <option value="" >select Year</option>
                                    @for ($year=$currentYear; $year>= $startYear; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                                <span id="yearError" class="text-danger"></span>
                            </div>
                            <div class="form-group mb-2">
                                <Label for="status">Payment Status <span class="text-danger">*</span></Label>
                                <select name="status" id="status" class="form-control  select2">
                                    <option value="">select status</option>
                                    <option value="Paid">Paid</option>
                                    <option value="Unpaid" selected>Unpaid</option>
                                    <option value="Partially Paid">Partially Paid</option>
                                </select>
                                <span id="statusError" class="text-danger"></span>
                            </div>

                        </div>

                        <div class="col-md-5">

                            <div class="form-group mb-2">
                                <label for="rent_amount">Rent Amount <span class="text-danger">*</span></label>
                                <input type="text" name="rent_amount" class="form-control" id="rent_amount" readonly>
                                <span id="rent_amountError" class="text-danger"></span>
                            </div>

                            
                               {{-- this is for dues  --}}
                            <div class="form-group mb-2">
                                <label for="dues">Dues<span class="text-danger">*</span></label>
                                <input type="text" name="dues" class="form-control" id="dues" readonly>
                                <span id="duesError" class="text-danger"></span>
                            </div>


                            {{-- this is for total --}}
                            <div class="form-group mb-2">
                                <label for="total">Total<span class="text-danger">*</span></label>
                                <input type="text" name="total" class="form-control" id="total" readonly>
                                <span id="totalError" class="text-danger"></span>
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
            <div class="card-header ">
                <h4>{{$title}} List</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="invoicesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Building</th>
                                <th>Customer</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Rent Amount</th>
                                <th>Paid</th>
                                <th>Balance</th>
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
    <div class="modal fade" id="transactionModal" tabindex="-1" role="dialog" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
            
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Transaction History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body" id="transactionModalBody">
                    <!-- Injected content will appear here -->
                </div>

            </div>
        </div>
    </div>

    <style>

        h2 {
            text-align: center;
            margin: 0;
        }
        .address {
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .meta {
            font-size: 14px;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .meta strong {
            display: inline-block;
            width: 130px;
        }
        .line-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .line-table td, .line-table th {
            border-bottom: 1px solid #000;
            padding: 8px 0;
        }
        .line-table td:first-child, .line-table th:first-child {
            text-align: left;
        }
        .line-table td:last-child, .line-table th:last-child {
            text-align: right;
        }
    </style>

    {{-- payment details modal  --}}
    <div class="modal fade lg" id="payNowModal" tabindex="-1" aria-labelledby="payNowModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                
                <form id="payInvoiceForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="payNowModalLabel">Payment Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">


                        <div class="meta">
                            <strong>Name:</strong> <span id="modalCustomerName"></span><br>
                            <strong>Month:</strong> <span id="modalMonth"></span><br>
                            <strong>Year:</strong> <span id="modalYear"></span><br>
                        </div>

                        <table class="line-table">
                            <tr>
                                <th>Paid</th>
                                <td id="modalPaid"></td>
                            </tr>

                            <tr>
                                <th>Remaining</th>
                                <td id="modalRemaining"></td>
                            </tr>

                            <tr>
                                <th>Rent Amount</th>
                                <td id="modalRentAmount"></td>
                            </tr>
                            </table>

                            <table>
                        
                        </table>

                        <div class="mb-3">
                            <label for="Paidamount" class="form-label">Payable Amount</label>
                            <input type="number" name="paid" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="Note" class="form-label">Note</label>
                            <textarea name="note" id="note" class="form-control" required></textarea>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>

 
</div>
@endsection
@push('js')
<script>
    $('#generateinvoices').click(function () {

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        const url = $(this).data('url');
        const building_id = $('#building_id').val();
        const month = $('#month').val();
        const year = $('#year').val();

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                building_id: building_id,
                month: month,
                year: year,
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message, "Success!", {
                        timeOut: 2000,
                        closeButton: true,
                        progressBar: true,
                        positionClass: "toast-top-right",
                        preventDuplicates: true,
                    });
                    
                    // 1. Reset the search form
                    $('#transactionForm')[0].reset(); // Replace with your form ID
                    
                    // 2. Refresh the transactions table
                    refreshTransactionsTable();
                } else {
                    toastr.error(res.message || "Operation failed", "Error!");
                }
            },
            error: function (err) {
                let errorMessage = err.responseJSON?.message || 'Failed to generate transactions';
                toastr.error(errorMessage, "Error!");
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Function to refresh the table
    function refreshInvoicesTable() {
        // If using DataTables
        if ($.fn.DataTable.isDataTable('#invoicesTable')) {
            $('#invoicesTable').DataTable().ajax.reload(null, false);
        } 
        // If manually loading table
        else {
            loadInvoices();
        }
    }


    // Set customerId globally (e.g., for edit form, from data attribute)
    let customerId = $('#customer_id').data('selected-id') || null;

    $('#building_id').on('change', function() {
        const buildingId = $(this).val();
        if (buildingId) {
            loadDependentDropdown('/customer-by-building', buildingId, 'customer_id', customerId, 'Select Customer', 'building_id');
        } else {
            $('#customer_id').html('<option value="">Select Customer</option>').trigger('change');
            $('#rent_amount').val('');
            $('#dues').val('');
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
                 
                    options += `<option data-dues="${item.dues}" data-rent="${item.rent_amount}" value="${item.id}">${item.name}</option>`;
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


    // When customer is selected, populate rent and dues
    $('#customer_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const dues = selectedOption.data('dues') || 0;
        const rent = selectedOption.data('rent') || 0;
        

        console.log(rent , dues)

        $('#rent_amount').val(rent);
        $('#dues').val(dues);
        
        // Calculate total immediately after populating
        calculateTotal();
    });

    // Calculate total when either rent or dues changes
    $('#rent_amount, #dues').on('input', function() {
        calculateTotal();
    });

    // Function to calculate and update total
    function calculateTotal() {
        const rent = parseFloat($('#rent_amount').val()) || 0;
        const dues = parseFloat($('#dues').val()) || 0;
        const subtotal = rent + dues;
        $('#total').val(subtotal); // Format to 2 decimal places
    }
   

    //Ready fucntion start
    $(document).ready(function() {
       
        $('#invoicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('invoices.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'building', name: 'building' },
                { data: 'customer', name: 'customer' },
                { data: 'month', name: 'month' },
                { data: 'year', name: 'year' },
                { data: 'rent_amount', name: 'rent_amount' },
                { data: 'paid', name: 'paid' },
                { data: 'remaining', name: 'remianing' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });
         
        //pay now button 
        $(document).on('click', '.payNowBtn', function (e) {
            e.preventDefault();

            const $form = $('#payInvoiceForm');
            $form.attr('action', $(this).data('url'));
            $form.append('<input type="hidden" name="_method" value="PUT">');
            
            const id = $(this).data('id');
            const name = $(this).data('name');
            const month = $(this).data('month');
            const year = $(this).data('year');
            const rent = $(this).data('rent_amount');
            const paid = $(this).data('paid');
            const remaining = $(this).data('remaining');

            $('#modalCustomerName').text(name);
            $('#modalMonth').text(month);
            $('#modalYear').text(year);
            $('#modalRentAmount').text(rent);
            $('#modalPaid').text(paid);
            $('#modalRemaining').text(remaining);

            $('#payNowModal').modal('show');
        });

        // Transaction history button 
       $(document).on('click', '.transactionHistoryBtn', function (e) {
            e.preventDefault();

            const id = $(this).data('id');
            const url = $(this).data('url');

            // Clear the modal body
            $('#transactionModalBody').html('<p>Loading...</p>');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {
                    if (response.transactions && response.transactions.length > 0) {
                        let html = '<table class="table table-bordered"><thead><tr><th>Customer</th><th>Month</th><th>Year</th><th>Paid</th><th>Dues</th><th>Note</th><th>Date</th></tr></thead><tbody>';
                        response.transactions.forEach(transaction => {
                            const dateObj = new Date(transaction.created_at);
                            const customerName = transaction.invoice?.customer?.name ?? '';
                            const formattedDate = dateObj.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            html += `<tr>
                                <td>${customerName}</td>
                                <td>${transaction.month}</td>
                                <td>${transaction.year}</td>
                                <td>${transaction.paid}</td>
                                <td>${transaction.dues}</td>
                                <td>${transaction.note ?? ''}</td>
                                <td>${formattedDate}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        $('#transactionModalBody').html(html);
                    } else {
                        $('#transactionModalBody').html('<p>No transaction history found.</p>');
                    }

                    // ✅ Show the modal
                    $('#transactionModal').modal('show');
                },
                error: function () {
                    $('#transactionModalBody').html('<p>Something went wrong.</p>');
                    $('#transactionModal').modal('show');
                }
            });
        });


      
    });
</script>
@endpush
