@extends('layouts.app')
@section('title', $title)

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title }}</h4>
                </div>
                <div class="card-body">
                    <form class="ajax-form" data-table="duesTable" action="{{ route('reports.dues') }}">
                        @csrf
                        <div class="form-group mb-2">
                            <label for="building">Building <span class="text-danger">*</span></label>
                            <select name="building_id" id="building_id" class="form-control single-select-placeholder select2">
                                <option value="">select building</option>
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="room_shop_id">Property <span class="text-danger">*</span></label>
                            <select name="room_shop_id" id="room_shop_id" class="form-control single-select-placeholder select2">

                            </select>
                        </div>
                        <button type="submit" id="submitBtn" class="btn btn-primary float-end submit-btn">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="duesTable" width="100%" cellspacing="0">
                            <thead>
                                <th>Building</th>
                                <th>Customer</th>
                                <th>Property</th>
                                <th>Total Dues</th>
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
        $('#building_id').on('change', function () {
            const buildingId = $(this).val();

            if (buildingId) {
                $.ajax({
                    url: "{{ route('depend') }}",
                    method: 'GET',
                    data: { building_id: buildingId },
                    success: function (data) {
                        const $roomShopSelect = $('#room_shop_id');
                        $roomShopSelect.empty();
                        $roomShopSelect.append('<option value="">Select Room/Shop</option>');

                        data.forEach(function (item) {
                            $roomShopSelect.append(`<option value="${item.id}">${item.name}</option>`);
                        });
                    }
                });
            }
        });


        $(document).ready(function () {
            // Initialize empty table
            let duesTable = $('#duesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reports.dues') }}",
                    data: function (d) {
                        d.building_id = $('#building_id').val();
                        d.room_shop_id = $('#room_shop_id').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error('AJAX Error:', xhr.responseText);
                    }
                },
                dom: 'Bfrtip',
                buttons: ['copy', 'csv', 'pdf', 'excel', 'print'],
                columns: [
                    { data: 'building', name: 'building' },
                    { data: 'customer', name: 'customer' },
                    { data: 'property', name: 'property' },
                    { data: 'total_dues', name: 'total_dues' }
                ]
            });

            // Handle form submission
            $('.ajax-form').on('submit', function(e) {
                e.preventDefault();
                
                let buildingId = $('#building_id').val();
                let roomShopId = $('#room_shop_id').val();

                if (!buildingId) {
                    alert('Please select a building before generating the report.');
                    return;
                }

                // Reload the table with new parameters
                duesTable.ajax.reload();
            });

        });

    </script>    
@endpush