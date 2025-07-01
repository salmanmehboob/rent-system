@extends('layouts.app')
@section('title', $title)

@section('content')
    <div class="reow">
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
        <div class="col-md-8 mx-5">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title }}</h4>
                </div>
                <div class="card body">
                    <form action="{{ route('agreement.set') }}" method="post" class="p-5">
                        @csrf
                        
                        <div class="form-group mb-2">
                            <label>Customer  <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class=" form-control single-select-placehoder select2">
                                <option value="" disabled selected> Select a Customer </option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                            <span id="customer_idError" class="text-danger"></span>
                        </div>

                            {{-- Agreement Section --}}
                        <div class="row">
                            <div class="col-md-5 mx-5">
                                <div class="form-group mb-2">
                                    <label>Property: <span class="text-danger">*</span></label>
                                    <select name="room_shop_id[]" class="form-control select2-multiple" multiple id="room_shop_id">
                                    <option value="" disabled selected></option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->type }}-{{ $room->no }}</option>
                                    @endforeach
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

                                <button type="submit" id="submitBtn" 
                                 class="d-none d-sm-inline-block btn btn btn-primary shadow-sm mt-4 px-5">Save</button>

                                <!-- Hidden status field -->
                                <input type="hidden" name="status" value="active">
                            </div>
                        </div>


                        
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('js')

    <script>
          //caculate duration on start date and end date
        $('#start_date, #end_date').on('change', function() {
            const start = new Date($('#start_date').val());
            const end = new Date($('#end_date').val());
            if (start && end && end > start) {
                const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
                $('#duration').val(months);
            }
        });

        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 4000);
    </script>

@endpush