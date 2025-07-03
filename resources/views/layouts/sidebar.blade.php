   <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Admin Dashboard</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item {{ Route::is('home') ? 'active' : ''}}">
                <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">



            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('buildings.index') ? 'active' : ''}}">
                <a class="nav-link {{ Route::is('buildings.index') ? 'active' : ''}}" href="{{ route('buildings.index') }}">
                    <i class="fas fa-city"></i>
                    <span>Buildings</span></a>
            </li>


            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('roomshops.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('roomshops.index') ? 'active' : '' }}" href="{{route('roomshops.index')}}">
                    <i class="fas fa-store "></i>
                    <span>Rooms Shops</span></a>
            </li>


            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('customers.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('customers.index') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="fas fa-users"></i>
                    <span>Customers</span></a>
            </li>


            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('invoices.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('invoices.index') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Invoices</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('bills') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('bills') ? 'active' : '' }}" href="{{ route('bills') }}">
                    <i class="fas fa-receipt"></i>
                    <span>Generating Bills</span></a>
            </li>

             <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('agreement.show') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('agreement.show') ? 'active' : '' }}" href="{{ route('agreement.show') }}">
                    <i class="fas fa-receipt"></i>
                    <span>Agreements</span></a>
            </li>

            <!-- Nav Item - Generate Excel Sheet -->
            <li class="nav-item {{ Route::is('excel.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('excel.index') ? 'active' : '' }}" href="{{ route('excel.index') }}">
                    <i class="fas fa-file-excel"></i>
                    <span>Generate Customer Sheet</span>
                </a>
            </li>

                   <!-- Divider -->
            <hr class="sidebar-divider">

              <!-- Nav Item - Utilities Collapse Menu -->
            @php
                $reportsActive = request()->routeIs('reports.customers') ||
                                request()->routeIs('reports.dues') ||
                                request()->routeIs('reports.buildings');
            @endphp

                <li class="nav-item">
                    <a class="nav-link {{ $reportsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                        aria-expanded="{{ $reportsActive ? 'true' : 'false' }}" aria-controls="collapseUtilities">
                        <i class="fas fa-chart-line"></i>
                        <span>Reports</span>
                    </a>
                    <div id="collapseUtilities" class="collapse {{ $reportsActive ? 'show' : '' }}" aria-labelledby="headingUtilities"
                        data-parent="#accordionSidebar">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item {{ request()->routeIs('reports.customers') ? 'active' : '' }}"
                                href="{{ route('reports.customers') }}">
                                <i class="fas fa-user-check mx-2"></i>
                                <span>Customer Reports</span>
                            </a>
                            <a class="collapse-item {{ request()->routeIs('reports.dues') ? 'active' : '' }}"
                                href="{{ route('reports.dues') }}">
                                <i class="fas fa-file-invoice-dollar mx-2"></i>
                                <span>Dues Reports</span>
                            </a>
                            <a class="collapse-item {{ request()->routeIs('reports.buildings') ? 'active' : '' }}"
                                href="{{ route('reports.buildings') }}">
                                <i class="fas fa-building mx-2"></i>
                                <span>Building Reports</span>
                            </a>
                        </div>
                    </div>
                </li>








        </ul>
