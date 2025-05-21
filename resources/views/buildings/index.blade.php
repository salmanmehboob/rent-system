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
                <form class="ajax-form" data-table="buildingsTable" action="{{ route('buildings.store') }}" method="POST">
                    @csrf

                    <div class="form-group mb-2">
                        <label>Building Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="buildingName" class="form-control" placeholder="Building Name">
                        <div id="nameError" class="text-danger mt-1"></div>
                    </div>

                    <div class="form-group mb-2">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Address">
                    </div>

                    <div class="form-group mb-2">
                        <label>Contact Number</label>
                        <input type="text" name="contact" class="form-control" placeholder="Contact Number">
                    </div>

                    <div class="form-group mb-2">
                        <label>Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Contact Person">
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
                    <table class="table table-bordered" id="buildingsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Person</th>
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
    $('#buildingsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('buildings.index') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'address', name: 'address' },
            { data: 'contact', name: 'contact' },
            { data: 'contact_person', name: 'contact_person' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false },
        ]
    });

    // Handle Edit Button Click
    $(document).on('click', '#editBtn', function(e) {
        e.preventDefault();
        let name = $(this).data('name');
        let address = $(this).data('address');
        let contact = $(this).data('contact');
        let contactPerson = $(this).data('contact_person');
        let formAction = $(this).data('url');

        const $form = $('form.ajax-form');
        $form.attr('action', formAction);
        $form.append('@method("PUT")');
        $form.find('input[name="name"]').val(name);
        $form.find('input[name="address"]').val(address);
        $form.find('input[name="contact"]').val(contact);
        $form.find('input[name="contact_person"]').val(contactPerson);
        $('#submitBtn').text('Update');
        $('#cancelBtn').removeClass('d-none');
    });

    // Handle Cancel Update
    $(document).on('click', '#cancelBtn', function() {
        const $form = $('form.ajax-form');
        $form.attr('action', "{{ route('buildings.store') }}");
        $form.find('input[name="_method"]').remove();
        $form[0].reset();
        $('#submitBtn').text('Save');
        $(this).addClass('d-none');
    });
});
</script>
@endpush
