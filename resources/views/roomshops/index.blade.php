@extends('layouts.app')
@section('title', $title)

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6>{{ $title }} Form</h6>
            </div>
            <div class="card-body">
                <form class="ajax-form" data-table="roomshopsTable" action="{{ route('roomshops.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-2">
                        <label>Building  <span class="text-danger">*</span></label>
                       <select name="building_id" class="form-control single-select-placehoder select2">
                        <option value="" disabled selected> Select a Building </option>
                        @foreach ($buildings as $building)
                            <option value="{{ $building->id }}">{{ $building->name }}</option>
                        @endforeach
                       </select>
                    </div>

                    <div class="form-group mb-2">
                        <label>Select Type <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-control single-select-placeholder select2">
                            <option value="" disabled selected>Select One Type</option>
                            <option value="room">Room</option>
                            <option value="shop">Shop</option>
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label>Availability <span class="text-danger">*</span></label>
                        <select name="availability" class="form-control single-select-placeholder select2">
                            <option value="1">Available</option>
                            <option value="0" disabled>Unavailable</option>
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label>Room/ Shop No <span class="text-danger">*</span></label>
                        <input type="text" name="no" class="form-control" placeholder="room/shop no">
                    </div>

                    
                    <button type="submit" id="submitBtn" class="btn btn-primary float-end submit-btn">Save</button>
                    <button type="button" id="cancelBtn" class="btn btn-secondary float-end me-2 d-none">Cancel Update</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6>{{ $title }} List</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="roomshopsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Building</th>
                                <th>Type</th>
                                <th>Room/Shop No</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
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
    $('#roomshopsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('roomshops.index') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'building', name: 'building' },
            { data: 'type', name: 'type' },
            { data: 'no', name: 'no' },
            { data: 'availability', name: 'availability' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ]
    });

    // Handle Edit Button Click
    $(document).on('click', '#editBtn', function(e) {
        e.preventDefault();
        let buildingId = $(this).data('building');
        let type = $(this).data('type');
        let no = $(this).data('no');
        let availability = $(this).data('availability');
        let formAction = $(this).data('url');

        const $form = $('form.ajax-form');
        $form.attr('action', formAction);
        $form.append('@method("PUT")');
        $form.find('select[name="building_id"]').val(buildingId).trigger('change');
        $form.find('select[name="type"]').val(type).trigger('change');
        $form.find('input[name="no"]').val(no);
        $form.find('select[name="availability"]').val(availability).trigger('change');
        $('#submitBtn').text('Update');
        $('#cancelBtn').removeClass('d-none');
    });

        // Handle Cancel Update or Add New Room/Shop
        $(document).on('click', '#cancelBtn', function () {
            const $form = $('form.ajax-form');
            
            // Reset form fields
            $form.trigger("reset");
            
            // Reset Select2 dropdowns
            $form.find('select').val(null).trigger('change');

            // Restore original form action and remove _method if exists
            $form.attr('action', "{{ route('roomshops.store') }}");
            $form.find('input[name="_method"]').remove();

            // Reset submit button text
            $('#submitBtn').text('Save');

            // Hide the cancel button
            $(this).addClass('d-none');
        });

});
</script>
@endpush
