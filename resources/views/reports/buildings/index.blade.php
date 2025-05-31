@extends('layouts.app')
@section('title', $title)

@section('content')

    <div class="row">

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                <h4>{{ $title }}</h4>
                </div>
                <div class="card-body">
                    <form class="ajax-form" data-table="buildingsTable" action="javascript:void(0);">
                        <div class="form-group mb-2">
                            <label for="building">Buildings <span class="text-danger">*</span></label>
                            <select name="building_id" id="building_id" class=" form-control single-select-placeholder select2">
                                <option value="">select an option</option>
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                      <button type="button" id="submitBtn" class="btn btn-primary float-end submit-btn">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="buildingsTable" width="100%" cellspacing="0">
                            <thead>
                                <th>Id</th>
                                <th>Building</th>
                                <th>Total Rooms</th>
                                <th>Total Shops</th>
                                <th>Available Rooms</th>
                                <th>Avaialable Shops</th>
                                <th>Total Rented</th>
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
        $(document).ready(function (){
            $('#buildingsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('reports.buildings') }}",
                dom: 'Bfrtip',
                buttons: [
                   'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                columns:[
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'building_name',
                        name: 'building_name'
                    },
                    {
                        data: 'total_rooms',
                        name: 'total_rooms'
                    },
                    {
                        data: 'total_shops',
                        name: 'total_shops'
                    },
                    {
                        data: 'available_rooms',
                        name: 'available_rooms'
                    },
                    {
                        data: 'available_shops',
                        name: 'available_shops'
                    },
                    {
                        data: 'total_rented_roomshops',
                        name: 'total_rented_roomshops'
                    }
                ]
            });
            $('#submitBtn').on('click', function () {
                let buildingId = $('#building_id').val();

                let table = $('#buildingsTable').DataTable();
                table.ajax.url("{{ route('reports.buildings') }}?building_id=" + buildingId).load();
            });

        });
    </script>
    
@endpush