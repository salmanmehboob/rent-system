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
                    <i class="fas fa-fw fa-table"></i>
                    <span>Buildings</span></a>
            </li>

            
            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('roomshops.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('roomshops.index') ? 'active' : '' }}" href="{{route('roomshops.index')}}">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Rooms/Shops</span></a>
            </li>

            
            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('customers.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('customers.index') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Customers</span></a>
            </li>

            
            <!-- Nav Item - Tables -->
            <li class="nav-item {{ Route::is('transactions.index') ? 'active' : '' }}">
                <a class="nav-link {{ Route::is('transactions.index') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Payment Transactions</span></a>
            </li>

                   <!-- Divider -->
            <hr class="sidebar-divider">

              <!-- Nav Item - Utilities Collapse Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
                    aria-expanded="true" aria-controls="collapseUtilities">
                    <i class="fas fa-fw fa-wrench"></i>
                    <span>Reports</span>
                </a>
                <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities"
                    data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="utilities-color.html">Customer Report</a>
                        <a class="collapse-item" href="utilities-border.html">Dues Report</a>
                        <a class="collapse-item" href="utilities-animation.html">Buildings Report</a>
                    </div>
                </div>
            </li>

            
       

     




        </ul>
