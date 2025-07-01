@extends('layouts.app')

@section('content')
    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
            <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#reportModal"><i
                class="fas fa-download fa-sm text-white-50"></i> Generate Report
            </button>
        </div>
        <!-- Modal -->
       <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-top">
                <div class="modal-content border-0 shadow-lg">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="reportModalLabel">
                            <i class="fas fa-chart-pie mr-2"></i>Select Report Type
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body bg-light">
                        <label for="reportType" class="font-weight-bold text-dark mb-2">Choose a Report</label>
                        <select id="reportType" class="form-control select2 mb-3" style="width: 100%;">
                            <option value="" disabled selected>Select report</option>
                            <option value="customers">Customer Reports</option>
                            <option value="dues">Dues Reports</option>
                            <option value="buildings">Building Reports</option>
                        </select>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times-circle mr-1"></i>Cancel
                        </button>
                        <button type="button" id="goToReport" class="btn btn-success">
                            <i class="fas fa-arrow-right mr-1"></i>Go
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="row">

                <!-- Stylish Last Bills Generated Card -->
            <div class="col-xl-12 col-md-12 mb-4">
                <div class="card shadow bg-gradient-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="font-weight-bold mb-1">Last Bills Generated</h5>
                                <p class="mb-0">
                                    {{ $lastBillsCount }} bill(s)
                                    @if($latestMonth && $latestYear)
                                        generated for <strong>{{ $latestMonth }}-{{ $latestYear }}</strong>.
                                    @endif
                                </p>
                            </div>

                            @if ($invoicesCount === 0)
                                <span class="badge badge-light p-2 fw-bold text-danger fs-5 text-uppercase">No bills available to be printed</span>
                            @else
                                <a href="{{ route('invoices.print.latest') }}" class="btn btn-warning">
                                    <i class="fas fa-print"></i> Print Bills
                                </a>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <!-- Buildings -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <a style="text-decoration:none;" href="{{ route('buildings.index') }}">
                        <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Buildings</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $buildingsCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-city fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </a>
                    </div>
                </div>
            </div>

            <!-- Rooms/Shops -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <a style="text-decoration:none;" href="{{ route('roomshops.index') }}">
                        <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Properties</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $roomhopsCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-door-open fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </a>
                    </div>
                </div>
            </div>

             <!-- Customers -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <a style="text-decoration:none;" href="{{ route('customers.index') }}">
                        <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Customers</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $customersCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tag fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </a>
                    </div>
                </div>
            </div>

              <!-- Transactions -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <a style="text-decoration:none;" href="{{ route('invoices.index') }}">
                        <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Invoices</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $invoicesCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-md-6">
            <h4>Total Invoices Amount: {{ number_format($total) }}</h4>
            <div id="invoiceChart" style="width: 100%; height: 250px; margin: auto;"></div>
        </div>
        <div class="col-md-6">
            <h4>Top 10 customers with highest dues</h4>
            <div id="topCustomersChart" style="width: 100%; height: 250px; margin: auto;"></div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Reports</46>
                </div>
                <div class="card-body">
                    <div id="formError" class="alert alert-danger d-none"></div>
                    <div class="table-responsive">
                        <table class="table table-bordered"  width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Total Collection</th>
                                    <th>Total Dues</th>
                                    <th>Total available rooms</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $invoices->sum('paid') }}</td>
                                    <td>{{ $invoices->sum('remaining') }}</td>
                                    <td>{{ $roomshops }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Expired Agreements</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered"  width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                     <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiredAgreements as $agreement)
                                    <tr>
                                        <td>{{ $agreement->customer->name }}</td>
                                         <td>{{ $agreement->start_date }}</td>   
                                        <td>{{ $agreement->end_date }}</td>
                                     @if($agreement->status === 'inactive')
                                        <td><span class="badge badge-danger">Expired</span></td>
                                    @else
                                        <td>{{ $agreement->status }}</td>
                                    @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Expiring Agreements</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered"  width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                     <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach ($expiringThisMonth as $agreement)
                                    <tr>
                                        <td>{{ $agreement->customer->name }}</td>
                                         <td>{{ $agreement->start_date }}</td>   
                                        <td>{{ $agreement->end_date }}</td> 
                                     @if($agreement->status === 'active')
                                        <td><span class="badge badge-success">Active</span></td>
                                    @else
                                        <td>{{ $agreement->status }}</td>  
                                    @endif
                                    </tr>
                                @endforeach
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
        $(document).ready(function () {
            $('.select2').select2({
             dropdownParent: $('#reportModal')
            });

            $('#goToReport').on('click', function () {
                let selected = $('#reportType').val();
                if (!selected) {
                    alert('Please select a report type.');
                    return;
                }

                switch (selected) {
                    case 'customers':
                    window.location.href = "{{ route('reports.customers') }}";
                    break;
                    case 'dues':
                    window.location.href = "{{ route('reports.dues') }}";
                    break;
                    case 'buildings':
                    window.location.href = "{{ route('reports.buildings') }}";
                    break;
                }
            });
        });

        am5.ready(function() {
            // Create root element
            var root = am5.Root.new("invoiceChart");

            // Set theme
            root.setThemes([
                am5themes_Animated.new(root)
            ]);

            // Create chart
            var chart = root.container.children.push(
                am5percent.PieChart.new(root, {
                    layout: root.verticalLayout
                })
            );

            // Create series
            var series = chart.series.push(
                am5percent.PieSeries.new(root, {
                    name: "Invoices",
                    valueField: "value",
                    categoryField: "status"
                })
            );

            // Set data (cast values as float in Blade to avoid issues)
            series.data.setAll([
                { status: "Paid", value: {{ (float) $paid }} },
                { status: "Dues", value: {{ (float) $dues }} }
            ]);

            // Labels with value only
            series.labels.template.setAll({
                text: "{category}: {value}"
            });

            // Tooltip with value only
            series.slices.template.setAll({
                tooltipText: "{category}: {value}"
            });

            // Add legend
            chart.children.push(
                am5.Legend.new(root, {
                    centerX: am5.p50,
                    x: am5.p50
                })
            );
        });




        am5.ready(function() {
            const topCustomersData = @json($topCustomers);
            var root = am5.Root.new("topCustomersChart");

            root.setThemes([am5themes_Animated.new(root)]);

            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    layout: root.verticalLayout
                })
            );

            // Add axes
            var xAxis = chart.xAxes.push(
                am5xy.ValueAxis.new(root, {
                    renderer: am5xy.AxisRendererX.new(root, {}),
                    tooltip: am5.Tooltip.new(root, {})
                })
            );

            var yAxis = chart.yAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "customer_name",
                    renderer: am5xy.AxisRendererY.new(root, {
                        minGridDistance: 20
                    })
                })
            );

            yAxis.data.setAll(topCustomersData);

            // Add series
            var series = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Dues",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueXField: "total_due",
                    categoryYField: "customer_name",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{categoryY}: {valueX}"
                    })
                })
            );

            series.columns.template.setAll({ tooltipText: "{categoryY}: {valueX}" });

            series.data.setAll(topCustomersData);

            // Make chart animate on load
            series.appear(1000);
            chart.appear(1000, 100);
            console.log(topCustomersData);
        });

    </script>
    
@endpush
