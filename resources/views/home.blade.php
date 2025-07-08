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

    <!-- Charts Row -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Total Invoices Amount: {{ number_format($total) }}</h6>
                </div>
                <div class="card-body">
                    <canvas id="invoiceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Customers with Highest Dues</h6>
                </div>
                <div class="card-body">
                    <canvas id="topCustomersChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Summary Row -->
    <!-- Report Summary Charts -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Collection Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyCollectionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Collection vs Dues (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="collectionTrendChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Building-wise Collection</h6>
                </div>
                <div class="card-body">
                    <canvas id="buildingCollectionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Agreement Charts Row -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Agreement Status Breakdown</h6>
                </div>
                <div class="card-body">
                    <canvas id="agreementStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Expiry Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyExpiryChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Expired Agreements Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="expiredAgreementsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-primary">Expiring Agreements (Next 3 Months)</h6>
                </div>
                <div class="card-body">
                    <canvas id="expiringAgreementsChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Expired Agreements Row -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Expired Agreements</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
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

    <!-- Expiring Agreements Row -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Expiring Agreements</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
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
 <script src="{{asset('assets/vendor/chart.js/chart-4-5.js')}}"></script>

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

    // Initialize Charts when DOM is ready
    document.addEventListener("DOMContentLoaded", function () {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        // Chart 1: Invoice Doughnut Chart with Chart.js 4.5 features
        const invoiceCtx = document.getElementById("invoiceChart");
        if (invoiceCtx) {
            new Chart(invoiceCtx, {
                type: "doughnut",
                data: {
                    labels: ["Paid", "Dues"],
                    datasets: [{
                        data: [{{ (float) $paid }}, {{ (float) $dues }}],
                        backgroundColor: [
                            "rgba(75, 192, 192, 0.8)",
                            "rgba(255, 99, 132, 0.8)"
                        ],
                        borderColor: [
                            "rgba(75, 192, 192, 1)",
                            "rgba(255, 99, 132, 1)"
                        ],
                        borderWidth: 2,
                        hoverBorderWidth: 3,
                        cutout: '60%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw.toLocaleString()} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 2: Top Customers Horizontal Bar Chart with Chart.js 4.5 features
        const topCustomers = @json($topCustomers);
        const labels = topCustomers.map(item => item.customer_name);
        const data = topCustomers.map(item => item.total_due);

        const topCustomersCtx = document.getElementById("topCustomersChart");
        if (topCustomersCtx) {
            new Chart(topCustomersCtx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Total Due",
                        data: data,
                        backgroundColor: "rgba(54, 162, 235, 0.8)",
                        borderColor: "rgba(54, 162, 235, 1)",
                        borderWidth: 1,
                        hoverBackgroundColor: "rgba(54, 162, 235, 1)",
                        borderRadius: 4,
                        borderSkipped: false
                    }]
                },
                options: {
                    indexAxis: "y", // horizontal bar
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    return `${context.label}: ${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                },
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 3: Monthly Collection Trend Chart
        const monthlyCollectionData = @json($monthlyCollection);
        const monthlyCollectionCtx = document.getElementById("monthlyCollectionChart");
        if (monthlyCollectionCtx && monthlyCollectionData.length > 0) {
            const labels = monthlyCollectionData.map(item => item.period);
            const paidData = monthlyCollectionData.map(item => item.paid);
            const remainingData = monthlyCollectionData.map(item => item.remaining);
            const totalData = monthlyCollectionData.map(item => item.total);

            new Chart(monthlyCollectionCtx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Paid Amount",
                            data: paidData,
                            borderColor: "#4bc0c0",
                            backgroundColor: "rgba(75, 192, 192, 0.15)",
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: "#4bc0c0"
                        },
                        {
                            label: "Remaining Amount",
                            data: remainingData,
                            borderColor: "#ff6384",
                            backgroundColor: "rgba(255, 99, 132, 0.15)",
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: "#ff6384"
                        },
                        {
                            label: "Total Amount",
                            data: totalData,
                            borderColor: "#36a2eb",
                            backgroundColor: "rgba(54, 162, 235, 0.10)",
                            borderWidth: 2,
                            fill: false,
                            borderDash: [8, 4],
                            tension: 0.4,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: "#36a2eb"
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                usePointStyle: true,
                                font: { size: 13 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.85)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    label += context.raw.toLocaleString('en-US', { style: 'currency', currency: 'PKR' });
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.08)",
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('en-US', { style: 'currency', currency: 'PKR', maximumFractionDigits: 0 });
                                },
                                font: { size: 12 }
                            }
                        },
                        x: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.08)",
                                drawBorder: false
                            },
                            ticks: { font: { size: 12 } }
                        }
                    },
                    animation: {
                        duration: 1200,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 4: Collection vs Dues Trend Chart
        const collectionTrendData = @json($collectionTrend);
        const collectionTrendCtx = document.getElementById("collectionTrendChart");
        if (collectionTrendCtx && collectionTrendData.length > 0) {
            const trendLabels = collectionTrendData.map(item => item.period);
            const collectionData = collectionTrendData.map(item => item.collection);
            const duesData = collectionTrendData.map(item => item.dues);

            new Chart(collectionTrendCtx, {
                type: "bar",
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: "Collection",
                        data: collectionData,
                        backgroundColor: "rgba(75, 192, 192, 0.8)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: "Dues",
                        data: duesData,
                        backgroundColor: "rgba(255, 99, 132, 0.8)",
                        borderColor: "rgba(255, 99, 132, 1)",
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 5: Building-wise Collection Chart
        const buildingCollectionData = @json($buildingCollection);
        const buildingCollectionCtx = document.getElementById("buildingCollectionChart");
        if (buildingCollectionCtx && Object.keys(buildingCollectionData).length > 0) {
            const buildingLabels = Object.keys(buildingCollectionData);
            const buildingPaidData = Object.values(buildingCollectionData).map(item => item.total_paid);
            const buildingRemainingData = Object.values(buildingCollectionData).map(item => item.total_remaining);

            new Chart(buildingCollectionCtx, {
                type: "bar",
                data: {
                    labels: buildingLabels,
                    datasets: [{
                        label: "Total Paid",
                        data: buildingPaidData,
                        backgroundColor: "rgba(75, 192, 192, 0.8)",
                        borderColor: "rgba(75, 192, 192, 1)",
                        borderWidth: 1,
                        borderRadius: 4
                    }, {
                        label: "Total Remaining",
                        data: buildingRemainingData,
                        backgroundColor: "rgba(255, 99, 132, 0.8)",
                        borderColor: "rgba(255, 99, 132, 1)",
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 6: Agreement Status Breakdown Chart
        const agreementStatusData = @json($agreementStatusData);
        const agreementStatusCtx = document.getElementById("agreementStatusChart");
        if (agreementStatusCtx && agreementStatusData.length > 0) {
            const statusLabels = agreementStatusData.map(item => item.status);
            const statusCounts = agreementStatusData.map(item => item.count);
            const colors = [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ];

            new Chart(agreementStatusCtx, {
                type: "doughnut",
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: colors.slice(0, statusLabels.length),
                        borderColor: colors.slice(0, statusLabels.length).map(color => color.replace('0.8', '1')),
                        borderWidth: 2,
                        hoverBorderWidth: 3,
                        cutout: '60%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.raw} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 7: Monthly Expiry Trend Chart
        const monthlyExpiryTrendData = @json($monthlyExpiryTrend);
        const monthlyExpiryCtx = document.getElementById("monthlyExpiryChart");
        if (monthlyExpiryCtx && monthlyExpiryTrendData.length > 0) {
            const expiryLabels = monthlyExpiryTrendData.map(item => item.period);
            const expiredData = monthlyExpiryTrendData.map(item => item.expired);
            const expiringData = monthlyExpiryTrendData.map(item => item.expiring);

            new Chart(monthlyExpiryCtx, {
                type: "line",
                data: {
                    labels: expiryLabels,
                    datasets: [{
                        label: "Expired",
                        data: expiredData,
                        borderColor: "rgba(255, 99, 132, 1)",
                        backgroundColor: "rgba(255, 99, 132, 0.2)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: "Expiring",
                        data: expiringData,
                        borderColor: "rgba(255, 205, 86, 1)",
                        backgroundColor: "rgba(255, 205, 86, 0.2)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw} agreements`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 8: Expired Agreements Trend Chart
        const expiredAgreementsData = @json($expiredAgreementsData);
        const expiredAgreementsCtx = document.getElementById("expiredAgreementsChart");
        if (expiredAgreementsCtx && expiredAgreementsData.length > 0) {
            const expiredLabels = expiredAgreementsData.map(item => item.period);
            const expiredCounts = expiredAgreementsData.map(item => item.expired_count);

            new Chart(expiredAgreementsCtx, {
                type: "bar",
                data: {
                    labels: expiredLabels,
                    datasets: [{
                        label: "Expired Agreements",
                        data: expiredCounts,
                        backgroundColor: "rgba(255, 99, 132, 0.8)",
                        borderColor: "rgba(255, 99, 132, 1)",
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} expired agreements`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Chart 9: Expiring Agreements Chart
        const expiringAgreementsData = @json($expiringAgreementsData);
        const expiringAgreementsCtx = document.getElementById("expiringAgreementsChart");
        if (expiringAgreementsCtx && expiringAgreementsData.length > 0) {
            const expiringLabels = expiringAgreementsData.map(item => item.period);
            const expiringCounts = expiringAgreementsData.map(item => item.expiring_count);

            new Chart(expiringAgreementsCtx, {
                type: "bar",
                data: {
                    labels: expiringLabels,
                    datasets: [{
                        label: "Expiring Agreements",
                        data: expiringCounts,
                        backgroundColor: "rgba(255, 205, 86, 0.8)",
                        borderColor: "rgba(255, 205, 86, 1)",
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw} expiring agreements`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            grid: {
                                color: "rgba(0, 0, 0, 0.1)",
                                drawBorder: false
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    });
</script>
@endpush
