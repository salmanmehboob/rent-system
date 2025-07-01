@extends('layouts.app')
@section('title', $title)

@section('content')
<div style="max-height: 500px; overflow-y: auto; overflow-x:hidden;">
    
     <div class="row mb-3">
       
        <div class="col-md-9 text-end">
            <button id="addCustomerBtn" class="btn btn-success">Add Customer</button>   
            <button id="viewCustomerBtn" class="btn btn-primary d-none">All Customers</button>  
        </div>
    </div>

     <div class="row d-none" id="customerFormDiv" >
        <div class="col-md-10 mx-5">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title}} Form</h4>
                </div>
                <div class="card-body">
                    <form class="ajax-form" id="customerForm" data-table="customersTable" action="{{ route('customers.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-5 mx-5">
                                <div class="form-group mb-2">
                                    <label>Building  <span class="text-danger">*</span></label>
                                    <select name="building_id" id="building_id" class=" form-control single-select-placehoder select2" style="width: 100%;">
                                        <option value="" disabled selected> Select a Building </option>
                                        @foreach ($buildings as $building)
                                            <option value="{{ $building->id }}">{{ $building->name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="building_idError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="name">Name: <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control" placeholder="Customer name">
                                    <div id="nameError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="mobile_no">Mobile No: <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile_no" id="mobile_no" class="form-control" placeholder="Mobile Number">
                                    <div id="mobile_noError" class="text-danger mt-1"></div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label for="cnic">CNIC No: <span class="text-danger">*</span></label>
                                    <input type="text" name="cnic" id="cnic" class="form-control" placeholder="CNIC No">
                                    <div id="cnicError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="address">Address: <span class="text-danger">*</span></label>
                                    <input type="text" name="address" id="address" class="form-control" placeholder="Address">
                                    <div id="addressError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <input type="hidden" name="status" id="status">
                                </div>
                            </div>
                        </div>
                        <hr>

                        {{-- Agreement Section --}}
                        <h4> Customer Agreement:</h4>
                        <div class="row">
                            <div class="col-md-5 mx-5">
                                <div class="form-group mb-2">
                                    <label>Property: <span class="text-danger">*</span></label>
                                    <select name="room_shop_id[]" class="form-control select2-multiple" multiple id="room_shop_id" style="width: 100%;">
                                        <!-- This will be filled dynamically with rooms based on selected building -->
                                    {{-- / <option value="">Select Room or Shop</option> --}}
                                    </select>
                                    <div id="room_shop_idError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <label>Start Date: <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" id="start_date">
                                    <div id="start_dateError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <label>End Date: <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" id="end_date">
                                    <div id="end_dateError" class="text-danger mt-1"></div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group mb-2">
                                    <label>Duration (months) <span class="text-danger">*</span></label>
                                    <input type="text" name="duration" class="form-control" id="duration" readonly>
                                    <div id="durationError" class="text-danger mt-1"></div>
                                </div>

                                <div class="form-group mb-2">
                                    <label>Monthly Rent: <span class="text-danger">*</span></label>
                                    <input type="text" name="monthly_rent" id="monthly_rent" class="form-control">
                                    <div id="monthly_rentError" class="text-danger mt-1"></div>
                                </div>

                                <!-- Hidden status field -->
                                <input type="hidden" name="status" value="active">
                            </div>
                        </div>
                        <hr>

                        {{-- Witness Section --}}
                        <h4>Witness Details</h4>
                        <div id="witnessRepeater">
                            <div data-repeater-list="witnesses">
                                <div data-repeater-item>
                                    <div class="row">
                                        <div class="col-md-5 mx-5">
                                            <input type="text" name="name" placeholder="Name" class="form-control mb-2">
                                            <input type="text" name="mobile_no" placeholder="Mobile No" class="form-control mb-2">
                                            <button data-repeater-delete type="button" class="btn btn-danger btn-sm">Delete</button>
                                        </div>

                                        <div class="col-md-5">
                                            <input type="text" name="cnic" placeholder="CNIC" class="form-control mb-2">
                                            <input type="text" name="address" placeholder="Address" class="form-control mb-2">
                                        </div>
                                    </div>
                                    <hr>
                                </div>
                            </div>
                            <button data-repeater-create type="button" class="btn btn-primary float-start btn-sm mb-3">Add Witness</button>
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
                   
                    <div class="row">
                        <div class="col-md-9">
                        <h4>{{$title}} List</h4>
                                            </div>
                        <div class="col-md-3 d-flex justify-content-end">
                            <select id="filterBuilding" class="form-control select2" style="min-width: 180px;">
                                <option value="">All Buildings</option>
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="formError" class="alert alert-danger d-none"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="customersTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Building</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>CNIC</th>
                                    <th>Address</th>
                                    {{-- Agreement Section --}}
                                    <th>Property</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Duration</th>
                                    <th>Rent</th>
                                    {{-- Witness Section --}}
                                    <!-- <th>Name</th>
                                    <th>Mobile</th>
                                    <th>CNIC</th>
                                    <th>Address</th> -->
                                    <th>Status</th>

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
</div>

@endsection
@push('js')
<script>
    //picks rooms or shops on seleted building
    $('#building_id').on('change', function () {
        const buildingId = $(this).val();

        if (buildingId) {
            loadDependentDropdown('/roomshop-by-building', buildingId, 'room_shop_id', null, 'Select Room or Shop', 'building_id');
        } else {
            $('#room_shop_id').html('<option value="">Select Room or Shop</option>');
        }
    });


    function loadDependentDropdown(url, parentId, childSelectId, selectedId = null, defaultText = 'Select', parentKey, selectedRoomShopId = null) {
        if (!parentKey) return;

        const data = { [parentKey]: parentId };

        // If on edit, include the selectedRoomShopId
        if (selectedRoomShopId) {
            data.selected_room_shop_id = selectedRoomShopId;
        }

        $.ajax({
            url: url,
            type: 'GET',
            data: data,
            success: function (data) {
                let options = `<option value="">${defaultText}</option>`;
                data.forEach(item => {
                    const isSelected = Array.isArray(selectedId)
                        ? selectedId.includes(item.id)
                        : selectedId == item.id;

                    options += `<option value="${item.id}" ${isSelected ? 'selected' : ''}>${item.name}</option>`;
                });
                $(`#${childSelectId}`).html(options).trigger('change');
            },
            error: function () {
                alert('Error loading options');
            }
        });
    }





    //caculate duration on start date and end date
    $('#start_date, #end_date').on('change', function() {
        const start = new Date($('#start_date').val());
        const end = new Date($('#end_date').val());
        if (start && end && end > start) {
            const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
            $('#duration').val(months);
        }
    });

    //Ready fucntion start
    $(document).ready(function() {
        var customersTable = $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('customers.index') }}",
                data: function(d) {
                    d.building_id = $('#filterBuilding').val();
                }
            },
            columns: [
                { data: 'building_name', name: 'building_name' },
                { data: 'name', name: 'name' },
                { data: 'mobile_no', name: 'mobile_no' },
                { data: 'cnic', name: 'cnic' },
                { data: 'address', name: 'address' },
                { data: 'property', name: 'property' },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                { data: 'duration', name: 'duration' },
                { data: 'monthly_rent', name: 'monthly_rent' },
                // { data: 'witnesses.0.name' },
                // { data: 'witnesses.0.mobile_no' },
                // { data: 'witnesses.0.cnic' },
                // { data: 'witnesses.0.address' },
                { data: 'status', name: 'status' },

                { data: 'actions', name: 'actions', orderable: false, searchable: false },
            ]
        });

        $('#filterBuilding').on('change', function() {
            customersTable.ajax.reload();
        });

        // Edit button
        $(document).on('click', '#editBtn', function(e) {
            e.preventDefault();
            // For debugging - check the form data structure
                console.log($(this).serializeArray());

            const $form = $('#customerForm');
            $form.attr('action', $(this).data('url'));
            $form.append('<input type="hidden" name="_method" value="PUT">');

            // Populate form
            $form.find('input[name="name"]').val($(this).data('name'));
            $form.find('input[name="mobile_no"]').val($(this).data('mobile_no'));
            $form.find('input[name="cnic"]').val($(this).data('cnic'));
            $form.find('input[name="address"]').val($(this).data('address'));
            $form.find('select[name="status"]').val($(this).data('status')).trigger('change');

            const buildingId = $(this).data('building');
            const roomShopId = $(this).data('room_shop_id');
            $form.find('select[name="building_id"]').val(buildingId).trigger('change');
            // Load room/shop dropdown and select value

            setTimeout(() => {
                loadDependentDropdown('/roomshop-by-building', buildingId, 'room_shop_id', roomShopId, 'Select Room or Shop', 'building_id', roomShopId );// this will trigger edit behavior
            }, 300);

            $form.find('input[name="start_date"]').val($(this).data('start_date'));
            $form.find('input[name="end_date"]').val($(this).data('end_date'));
            $form.find('input[name="duration"]').val($(this).data('duration'));
            $form.find('input[name="monthly_rent"]').val($(this).data('monthly_rent'));

            // Populate witnesses
            const witnesses = $(this).data('witnesses');
            const repeaterList = $('[data-repeater-list="witnesses"]');
            repeaterList.empty();
            if (witnesses && witnesses.length > 0) {
                witnesses.forEach((witness, index) => {
                    const witnessItem = `
                        <div data-repeater-item>
                            <div class="row">
                                <div class="col-md-5 mx-5">
                                    <input type="hidden" name="witnesses[${index}][id]" value="${witness.id}">
                                    <input type="text" name="witnesses[${index}][name]" value="${witness.name}" placeholder="Name" class="form-control mb-2">
                                    <input type="text" name="witnesses[${index}][mobile_no]" value="${witness.mobile_no}" placeholder="Mobile No" class="form-control mb-2">
                                    <button data-repeater-delete type="button" class="btn btn-danger btn-sm">Delete</button>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="witnesses[${index}][cnic]" value="${witness.cnic}" placeholder="CNIC" class="form-control mb-2">
                                    <input type="text" name="witnesses[${index}][address]" value="${witness.address}" placeholder="Address" class="form-control mb-2">
                                </div>
                            </div>
                            <hr>
                        </div>`;
                    repeaterList.append(witnessItem);
                });
            }

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

            // Reset witnesses with correct naming structure
            $('#witnessRepeater [data-repeater-list]').html(`
                <div data-repeater-item>
                    <div class="row">
                        <div class="col-md-5 mx-5">
                            <input type="text" name="witnesses[0][name]" placeholder="Name" class="form-control mb-2">
                            <input type="text" name="witnesses[0][mobile_no]" placeholder="Mobile No" class="form-control mb-2">
                            <button data-repeater-delete type="button" class="btn btn-danger btn-sm">Delete</button>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="witnesses[0][cnic]" placeholder="CNIC" class="form-control mb-2">
                            <input type="text" name="witnesses[0][address]" placeholder="Address" class="form-control mb-2">
                        </div>
                    </div>
                    <hr>
                </div>
            `);
            $('#customersTable').DataTable().ajax.reload(null, false);

            $('#customerFormDiv').addClass('d-none');
            $('.card:has(#customersTable)').parent().parent().removeClass('d-none'); // Show the list card
            $('#addCustomerBtn').removeClass('d-none'); // Show the add button
        });

        // Initialize repeater (correct placement)
        $('#witnessRepeater').repeater({
            initEmpty: false,
            defaultValues: {
                'name': '',
                'mobile_no': '',
                'cnic': '',
                'address': ''
            },
            show: function() {
                $(this).slideDown();
                const item = $(this);
                const index = item.parent('[data-repeater-list]').children('[data-repeater-item]').length - 1;

                item.find('[name="name"]').attr('name', `witnesses[0][name]`);
                item.find('[name="mobile_no"]').attr('name', `witnesses[0][mobile_no]`);
                item.find('[name="cnic"]').attr('name', `witnesses[0][cnic]`);
                item.find('[name="address"]').attr('name', `witnesses[0][address]`);
            },
            hide: function() {
                $(this).slideUp();
                setTimeout(() => {
                    $('[data-repeater-item]').each(function(index) {
                        $(this).find('[name^="witnesses["]').each(function() {
                            const name = $(this).attr('name');
                            const newName = name.replace(/witnesses\[\d+\]/, `witnesses[${index}]`);
                            $(this).attr('name', newName);
                        });
                    });
                }, 300);
            }
        });

        // Add Customer button click handler
        $('#addCustomerBtn').on('click', function(e) {
            e.preventDefault();
            $('#customerFormDiv').removeClass('d-none');
            $('.card:has(#customersTable)').parent().parent().addClass('d-none'); // Hide the list card
            $(this).addClass('d-none'); // Hide the add button
            $('#viewCustomerBtn').removeClass('d-none'); // Show the view button        
        });

        // View Customer button click handler   
        $('#viewCustomerBtn').on('click', function(e) {
            e.preventDefault();
            $('#customerFormDiv').addClass('d-none');
            $('.card:has(#customersTable)').parent().parent().removeClass('d-none'); // Show the list card  
            $('#addCustomerBtn').removeClass('d-none'); // Show the add button
            $('#viewCustomerBtn').addClass('d-none'); // Hide the view button   
        });

        // Initialize select2 with allowClear for building filter
        $('#filterBuilding').select2({
            placeholder: 'All Buildings',
            allowClear: true
        });
    });
</script>   
@endpush
