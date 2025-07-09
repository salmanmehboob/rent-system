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
<div class="row mb-3">
    <div class="col-md-12">
        <button id="addInvoiceBtn" class="btn btn-success">Add Invoice</button>
        <button id="viewInvoiceBtn" class="btn btn-primary d-none">All Invoices</button>
    </div>
</div>
<div style="max-height: 700px; overflow-y: auto; overflow-x:hidden;">
    <div class="row d-none" id="invoiceFormDiv">
        <div class="col-md-10 mx-5">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title}} Form</h4>
                </div>
                <div class="card-body">
                    <div id="formError" class="alert alert-danger d-none"></div>
                    <form class="ajax-form" id="invoiceForm" data-table="invoicesTable" action="{{ route('invoices.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" id="invoice_id" name="invoice_id">
                            <div class="col-md-5 mx-5">
                                <div class="form-group mb-2">
                                    <label>Building  <span class="text-danger">*</span></label>
                                    <select name="building_id" id="building_id" class=" form-control single-select-placehoder select2" style="width: 100%;">
                                        <option value="" disabled selected> Select a Building </option>
                                        @foreach ($buildings as $building)
                                            <option value="{{ $building->id }}">{{ $building->name }}</option>
                                        @endforeach
                                    </select>
                                    <span id="building_idError" class="text-danger"></span>
                                </div>

                                <div class="form-group mb-2">
                                    <label>Customer <span class="text-danger">*</span></label>
                                    <select name="customer_id" class="form-control select2" id="customer_id" style="width: 100%;">
                                        <!-- This will be filled dynamically with customer based on selected building -->
                                        <option value="">Select Customer</option>
                                    </select>
                                    <span id="customer_idError" class="text-danger"></span>
                                </div>


                                <div class="form-group mb-2">
                                    <label for="month">Month <span class="text-danger">*</span></label>
                                    <select name="month" id="month" class="form-control select2" style="width: 100%;">
                                        <option value="">select month</option>
                                        @foreach ($months as $month)
                                            <option value="{{ $month }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    <span id="monthError" class="text-danger"></span>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="year">Year <span class="text-danger">*</span></label>
                                    <select name="year" id="year" class="form-control select2" style="width: 100%;">
                                        <option value="" >select Year</option>
                                        @for ($year=$currentYear; $year>= $startYear; $year--)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endfor
                                    </select>
                                    <span id="yearError" class="text-danger"></span>
                                </div>
                                <div class="form-group mb-2">
                                    <Label for="status">Payment Status <span class="text-danger">*</span></Label>
                                    <select name="status" id="status" class="form-control  select2" style="width: 100%;">
                                        <option value="Paid">Paid</option>
                                        <option value="Unpaid" selected>Unpaid</option>
                                        <option value="Partially Paid">Partially Paid</option>
                                        <option value="Dues Adjusted">Dues Adjusted</option>
                                    </select>
                                    <span id="statusError" class="text-danger"></span>
                                </div>

                            </div>

                            <div class="col-md-5">

                                <div class="form-group mb-2">
                                    <label for="rent_amount">Rent Amount <span class="text-danger">*</span></label>
                                    <input type="text" name="rent_amount" class="form-control" id="rent_amount">
                                    <span id="rent_amountError" class="text-danger"></span>
                                </div>


                                {{-- this is for dues  --}}
                                <div class="form-group mb-2">
                                    <label for="dues">Previous Dues<span class="text-danger">*</span></label>
                                    <input type="text" name="dues" class="form-control" id="dues">
                                    <span id="duesError" class="text-danger"></span>
                                </div>


                                {{-- this is for total --}}
                                <div class="form-group mb-2">
                                    <label for="total">Total<span class="text-danger">*</span></label>
                                    <input type="text" name="total" class="form-control" id="total">
                                    <span id="totalError" class="text-danger"></span>
                                </div>


                            </div>
                        </div>


                        <button type="submit" id="submitBtn" class="btn btn-primary float-right m-2 submit-btn px-4">Save</button>
                        <button type="button" id="cancelBtn" class="btn btn-secondary float-right m-2 d-none">Cancel Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <div class="row" id="invoiceListDiv">
        <div class="col-md-12 my-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4>{{$title}} List</h4>
                    <div class="d-flex align-items-center gap-3">
                        <div class="m-3">
                            <select id="filterBuilding" class="form-control select2" style="min-width: 180px;">
                                <option value="">Select Buildings</option>
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="m-3">
                            <select id="filterCustomer" class="form-control select2" style="min-width: 180px;">
                                <option value="">Select Customers</option>
                            </select>
                        </div>

                    <div class="m-3">
                        <select id="filterStatus" class="form-control select2" style="min-width: 180px;">
                            <option value="">Select Status</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partially Paid">Partially Paid</option>
                            <option value="Dues Adjusted">Dues Adjusted</option>
                        </select>
                    </div>
                    <div class="m-3">
                        <select id="filterMonth" class="form-control select2" style="min-width: 180px;">
                            <option value="">Select Months</option>
                            @foreach ($months as $month)
                                <option value="{{ $month }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="m-3">
                        <select id="filterYear" class="form-control select2" style="min-width: 180px;">
                            <option value="">Select Years</option>
                            @for ($year=$currentYear; $year>= $startYear; $year--)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    </div>
                </div>
                <div class="card-body">
                    <button id="payNowCombineBtn" class="btn btn-success mb-3 d-none">Pay Now Combine</button>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="invoicesTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="no-sort"><input type="checkbox" id="checkAllInvoices"></th> <!-- Check All -->
                                    <th>Building</th>
                                    <th>Customer</th>
                                    <th>Room/Shops</th>
                                    <th>Month</th>
                                    <th>Rent</th>
                                    <th>Dues</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Type</th>
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

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="TransactionModalLabel">
                            <i class="fas fa-chart-pie mr-2"></i>Transaction History
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
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
            th.no-sort.sorting, th.no-sort.sorting_asc, th.no-sort.sorting_desc {
                pointer-events: none;
                cursor: default;
                background-image: none !important;
            }
            /* Hide DataTables sorting icons for .no-sort columns */
            th.no-sort:after, th.no-sort:before {
                display: none !important;
                content: none !important;
            }
            th.no-sort {
                cursor: default !important;
                background-image: none !important;
            }
        </style>

        {{-- payment details modal  --}}
        <div class="modal fade lg" id="payNowModal" tabindex="-1" aria-labelledby="payNowModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <form id="payInvoiceForm" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title font-weight-bold" id="reportModalLabel">
                                <i class="fas fa-chart-pie mr-2"></i>Payment Details
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">


                            <div class="meta">
                                <strong>Name:</strong> <span id="modalCustomerName"></span><br>
                                <strong>Month:</strong> <span id="modalMonth"></span>-<span id="modalYear"></span><br>
                            </div>

                            <table class="line-table">
                                <tr>
                                    <th>Previous Dues</th>
                                    <td id="modalDues"></td>
                                </tr>

                                <tr>
                                    <th>Rent Amount</th>
                                    <td id="modalRentAmount"></td>
                                </tr>

                                <tr>
                                    <th>Paid</th>
                                    <td id="modalPaid"></td>
                                </tr>

                                <tr>
                                    <th>Total</th>
                                    <td id="modalRemaining"></td>
                                </tr>
                                </table>

                                <table>

                            </table>

                            <div class="mb-3">
                                <label for="Paidamount" class="form-label">Payable Amount</label>
                                <input type="number" id="paid" name="paid" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="Note" class="form-label">Note</label>
                                <textarea name="note" id="note" class="form-control" required>Paid</textarea>
                        </div>


                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times-circle mr-1"></i>Cancel
                            </button>
                            <button type="submit"  class="btn btn-success">
                                <i class="fas fa-arrow-right mr-1"></i>Submit Payment
                            </button>
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
        const invoice_id = $('#invoice_id').val();
        if (buildingId) {
            loadDependentDropdown('/customer-by-building', buildingId, 'customer_id', customerId, 'Select Customer', 'building_id', invoice_id);
        } else {
            $('#customer_id').html('<option value="">Select Customer</option>').trigger('change');
            $('#rent_amount').val('');
            $('#dues').val('');
        }
    });

    function loadDependentDropdown(url, parentId, childSelectId, selectedId = null, defaultText = 'Select', parentKey, invoiceID) {
        if (!parentKey) return;

        const data = { [parentKey]: parentId };
        if (invoiceID) {
            data.invoice_id = invoiceID;
        }
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

                // If we're in edit mode and have a selectedId, set it after populating
                if (selectedId && invoiceID) {
                    setTimeout(() => {
                        $select.val(selectedId).trigger('change');
                    }, 100);
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
        console.log('calculateTotal' )
        console.log(  rent , dues)
        $('#total').val(subtotal.toFixed(2)); // Format to 2 decimal places
    }


    // --- Building/Customer filter logic ---
    $('#filterBuilding').on('change', function() {
        const buildingId = $(this).val();
        // Reset customer dropdown
        $('#filterCustomer').html('<option value="">All Customers</option>').trigger('change');
        if (buildingId) {
            // Fetch customers for this building
            $.ajax({
                url: '/customer-by-building',
                type: 'GET',
                data: { building_id: buildingId },
                success: function(data) {
                    let options = '<option value="">All Customers</option>';
                    data.forEach(function(item) {
                        options += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $('#filterCustomer').html(options).trigger('change.select2');
                },
                error: function() {
                    toastr.error('Failed to load customers');
                }
            });
        }
        // Trigger DataTable filter
        $('#invoicesTable').DataTable().ajax.reload();
    });

    $('#filterCustomer').on('change', function() {
        $('#invoicesTable').DataTable().ajax.reload();
    });

    // --- DataTable with filter params ---
    $(document).ready(function() {
        $('#invoicesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('invoices.index') }}",
                data: function(d) {
                    d.building_id = $('#filterBuilding').val();
                    d.customer_id = $('#filterCustomer').val();
                    d.status = $('#filterStatus').val();
                    d.month = $('#filterMonth').val();
                    d.year = $('#filterYear').val();
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'no-sort', // prevent ordering arrow
                    render: function (data, type, row) {
                        if (row.status && row.status.indexOf('Unpaid') === -1) {
                            return '';
                        }
                        return '<input type="checkbox" class="invoice-checkbox" value="' + row.invoice_id + '">';
                    }
                },
                { data: 'building', name: 'building' },
                { data: 'customer', name: 'customer' },
                { data: 'room_shops', name: 'room_shops' },
                {
                    data: null,
                    name: 'month_year',
                    render: function (data, type, row) {
                        return row.month + '-' + row.year;
                    }
                },
                { data: 'rent_amount', name: 'rent_amount' },
                { data: 'dues', name: 'dues' },
                { data: 'paid', name: 'paid' },
                { data: 'remaining', name: 'remianing' },
                { data: 'status', name: 'status' },
                { data: 'type', name: 'type' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 0, orderable: false, searchable: false, className: 'no-sort' }
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
            const dues = $(this).data('dues');
            const remaining = $(this).data('remaining');

            $('#modalCustomerName').text(name);
            $('#modalMonth').text(month);
            $('#modalYear').text(year);
            $('#modalRentAmount').text(rent);
            $('#modalPaid').text(paid);
            $('#modalDues').text(dues);
            $('#modalRemaining').text(remaining);
            $('#paid').val(remaining);

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
                    id : id
                },
                success: function (response) {
                    if (response.transactions && response.transactions.length > 0) {
                        let html = '<table class="table table-bordered"><thead><tr><th>Customer</th><th>Month</th><th>Paid</th><th>Dues</th><th>Note</th><th>Date</th></tr></thead><tbody>';
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
                                <td>${transaction.month}-${transaction.year}</td>
                                <td>${transaction.paid}</td>
                                <td>${transaction.remaining}</td>
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

        // Add Invoice button click handler
        $('#addInvoiceBtn').on('click', function(e) {
            e.preventDefault();
            $('#invoiceFormDiv').removeClass('d-none');
            $('#invoiceListDiv').addClass('d-none');
            $('#addInvoiceBtn').addClass('d-none');
            $('#viewInvoiceBtn').removeClass('d-none');
        });

        // View Invoice button click handler
        $('#viewInvoiceBtn').on('click', function(e) {
            e.preventDefault();
            $('#invoiceFormDiv').addClass('d-none');
            $('#invoiceListDiv').removeClass('d-none');
            $('#addInvoiceBtn').removeClass('d-none');
            $('#viewInvoiceBtn').addClass('d-none');
        });

        // Edit Invoice button click handler
        $(document).on('click', '.editInvoiceBtn', function(e) {
            e.preventDefault();
            // Show the form and hide the list
            $('#invoiceFormDiv').removeClass('d-none');
            $('#invoiceListDiv').addClass('d-none');
            $('#addInvoiceBtn').addClass('d-none');
            $('#viewInvoiceBtn').removeClass('d-none');

            // Store the customer ID to set after building loads
            const customerIdToSet = $(this).data('customer_id');

            $('#invoice_id').val($(this).data('id'));
            $('#month').val($(this).data('month')).trigger('change');
            $('#year').val($(this).data('year')).trigger('change');
            $('#rent_amount').val($(this).data('rent_amount'));
            $('#dues').val($(this).data('dues'));
            $('#total').val((parseFloat($(this).data('rent_amount')) + parseFloat($(this).data('dues'))).toFixed(2));
            // Map status value to match select options
            const statusMap = {
                'paid': 'Paid',
                'unpaid': 'Unpaid',
                'partially_paid': 'Partially Paid',
                'Paid': 'Paid',
                'Unpaid': 'Unpaid',
                'Partially Paid': 'Partially Paid'
            };
            const statusValue = statusMap[$(this).data('status')] || $(this).data('status');
            $('#status').val(statusValue).trigger('change');

            // Fill the form fields with invoice data
            $('#building_id').val($(this).data('building_id')).trigger('change');

            // Wait for customer dropdown to populate and then set the customer
            setTimeout(() => {
                $('#customer_id').val(customerIdToSet).trigger('change');
            }, 500);

            // Set form action to update
            $('#invoiceForm').attr('action', $(this).data('url'));
            // Add hidden _method input for PUT
            if ($('#invoiceForm input[name="_method"]').length === 0) {
                $('#invoiceForm').append('<input type="hidden" name="_method" value="PUT">');
            } else {
                $('#invoiceForm input[name="_method"]').val('PUT');
            }
            // Show cancel button
            $('#cancelBtn').removeClass('d-none');
        });

        // Cancel Update button click handler
        $('#cancelBtn').on('click', function() {
            // Reset form and UI to add mode
            $('#invoiceForm')[0].reset();
            $('#invoiceForm').attr('action', "{{ route('invoices.store') }}");
            $('#invoiceForm input[name="_method"]').remove();
            $('#cancelBtn').addClass('d-none');
            $('#invoiceFormDiv').addClass('d-none');
            $('#invoiceListDiv').removeClass('d-none');
            $('#addInvoiceBtn').removeClass('d-none');
            $('#viewInvoiceBtn').addClass('d-none');

            // Clear customer dropdown and reset related fields
            $('#customer_id').html('<option value="">Select Customer</option>').trigger('change');
            $('#rent_amount').val('');
            $('#dues').val('');
            $('#total').val('');
        });

        // Initialize select2 with allowClear for all relevant selects
        $('.select2').select2({
            allowClear: true,
            placeholder: function(){
                return $(this).attr('placeholder') || 'Select';
            }
        });

        $('#filterMonth, #filterYear, #filterStatus').on('change', function() {
            $('#invoicesTable').DataTable().ajax.reload();
        });
    });

    // Checkbox logic and Pay Now Combine button
    $(document).on('change', '#checkAllInvoices', function() {
        var checked = $(this).is(':checked');
        // Only check visible (enabled) checkboxes
        $('.invoice-checkbox').prop('checked', checked).trigger('change');
    });

    $(document).on('change', '.invoice-checkbox', function() {
        var total = $('.invoice-checkbox').length;
        var checkedCount = $('.invoice-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#payNowCombineBtn').removeClass('d-none');
        } else {
            $('#payNowCombineBtn').addClass('d-none');
        }
        // If all visible checkboxes are checked, check the header; else, uncheck
        $('#checkAllInvoices').prop('checked', total > 0 && total === checkedCount);
    });

    $('#payNowCombineBtn').on('click', function() {
        var selectedIds = $('.invoice-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) return;
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to pay ' + selectedIds.length + ' invoices together.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Pay Now!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('invoices.pay-now-combine') }}",
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        invoice_ids: selectedIds
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, "Success!", {
                                timeOut: 2000,
                                closeButton: true,
                                progressBar: true,
                                positionClass: "toast-top-right",
                                preventDuplicates: true,
                            });
                            // Refresh the table
                            refreshInvoicesTable();
                            // Close modal if it was open
                            $('#payNowModal').modal('hide');
                        } else {
                            toastr.error(response.message || "Operation failed", "Error!");
                        }
                    },
                    error: function(err) {
                        let errorMessage = err.responseJSON?.message || 'Failed to pay invoices';
                        toastr.error(errorMessage, "Error!");
                    }
                });
            }
        });
    });

</script>
@endpush
