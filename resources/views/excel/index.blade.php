@extends('layouts.app')
@section('title', 'Generate Excel Sheet')

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h2>Generate Customer Sheet</h2>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="mb-4">
                        <div class="form-group mb-2">
                            <label for="building_id">Building</label>
                            <select name="building_id" id="building_id" class="form-control select2">
                                <option value="">Select a Building</option>
                                @foreach ($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="month">Month</label>
                            <select name="month" id="month" class="form-control select2">
                                <option value="">Select Month</option>
                                @foreach ($months as $month)
                                    <option value="{{ $month }}">{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="year">Year</label>
                            <select name="year" id="year" class="form-control select2">
                                <option value="">Select Year</option>
                                @for ($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="button" id="filterBtn" class="btn btn-primary mt-2">Filter</button>
                    </form>
                    <button type="button" id="downloadPdfBtn" class="btn btn-success mb-3 d-none">Download PDF</button>
                    <button type="button" id="printBtn" class="btn btn-success mb-3 d-none">Print</button>

                    <div class="table-responsive d-none" id="customerDiv">

                        <div class="text-center mb-3">
                            <h4><strong id="buildingName">CONTINENTAL PLAZA</strong></h4>
                            <p id="buildingAddress">MAKANBAGH MINGORA SWAT</p>
                            <p id="buildingContact"><strong>RAHAT ALI KHAN:</strong> 0332-3000222 / 0946-723905</p>
                            <h5 class="mt-3">MONTH: <strong id="selectedMonthYear">---</strong></h5>
                        </div>


                        <table class="table table-bordered" id="customersTable">
                            <thead class="thead-dark">
                            <tr>
                                <th>S.NO</th>
                                <th>NAME</th>
                                <th>SHOP#</th>
                                <th>MOBILE</th>
                                <th>RENT</th>
                                <th>DUES</th>
                                <th>PAID</th>
                                <th>REMARKS</th>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Add validation to ensure building, month, and year are selected before filtering
            $('#filterBtn').on('click', function (e) {
                let building_id = $('#building_id').val();
                let month = $('#month').val();
                let year = $('#year').val();

                if (!building_id || !month || !year) {
                    alert('Please select Building, Month, and Year.');
                    return false;
                }
                // The rest of the AJAX logic is handled below

                $('#selectedMonthYear').text(`${month} ${year}`);

                fetchCustomers(building_id, month, year); // example usage

            });

            function fetchCustomers(building_id, month, year) {
                $.ajax({
                    url: '{{ route('excel.index') }}',
                    type: 'GET',
                    data: {
                        building_id: building_id,
                        month: month,
                        year: year,
                        ajax: 1
                    },
                    success: function (response) {
                        const building = response.building;
                        const customers = response.customers;

                        // Update building header
                        $('#buildingName').text(building.name || 'N/A');
                        $('#buildingAddress').text(building.address || 'N/A');
                        $('#buildingContact').html(`<strong>${building.contact_person || ''}:</strong> ${building.phone || ''}`);

                        $('#customerDiv').removeClass('d-none');
                        // Fill the table
                        let tbody = '';
                        if (customers.length === 0) {
                            tbody = '<tr><td colspan="8" class="text-center">No data available</td></tr>';
                        } else {
                            customers.forEach(function (row, index) {
                                tbody += `
                                        <tr>
                                            <td>${index + 1}</td>
                                            <td>${row.customer_name}</td>
                                            <td>${row.roomshop_name}</td>
                                            <td>${row.mobile_no}</td>
                                            <td>${row.rent}</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>`;
                            });
                        }

                        $('#customersTable tbody').html(tbody);
                        $('#downloadPdfBtn').removeClass('d-none');
                        $('#printBtn').removeClass('d-none');
                    },
                    error: function () {
                        alert('Failed to fetch data. Please try again.');
                    }
                });
            }

            $('#downloadPdfBtn').on('click', function () {
                // Remove any previous page-break classes
                $('#customersTable tbody tr').removeClass('page-break');
                // Add page-break class every 25 rows (adjust as needed)
                $('#customersTable tbody tr').each(function (i) {
                    if (i > 0 && i % 25 === 0) {
                        $(this).addClass('page-break');
                    }
                });

                var element = document.getElementById('customerDiv');
                var opt = {
                    margin: 0.2,
                    filename: 'customers.pdf',
                    image: {type: 'jpeg', quality: 0.98},
                    html2canvas: {scale: 2},
                    jsPDF: {unit: 'in', format: 'a4', orientation: 'portrait'},
                    pagebreak: {mode: ['css', 'legacy'], before: '.page-break'}
                };
                setTimeout(function () {
                    html2pdf().set(opt).from(element).save();
                }, 200);
            });

            $('#printBtn').on('click', function () {
                const printContents = document.getElementById('customerDiv').innerHTML;
                const printWindow = window.open('', '', 'height=800,width=1000');
                printWindow.document.write('<html><head><title>Customer Report</title>');
                printWindow.document.write(`
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { border-collapse: collapse; width: 100%; }
                        th, td { border: 1px solid #000; padding: 6px; text-align: center; font-size: 12px; }
                        h4, h5, p { margin: 4px 0; text-align: center; }
                    </style>
                `);
                printWindow.document.write('</head><body>');
                printWindow.document.write(printContents);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            });


        });
    </script>
@endpush
