	<aside class="col-md-4 col-lg-3">
        <ul class="nav nav-dashboard flex-column mb-3 mb-md-0" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ (\Request::route()->getName() == 'customer.dashboard.index') ?  'active': ''}}"  href="{{ route('customer.dashboard.index') }}">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (\Request::route()->getName() == 'customer.orders.index') ?  'active': ''}}"  href="{{ route('customer.orders.index') }}" >Orders</a>
            </li>
            <li class="nav-item">
                {{-- <a class="nav-link {{ (\Request::route()->getName() == 'profile.index') ?  'active': ''}}" id="tab-downloads-link" data-toggle="tab" href="#tab-downloads" role="tab" aria-controls="tab-downloads" aria-selected="false">Downloads</a> --}}
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (\Request::route()->getName() == 'dashboard_my_addresses') ?  'active': ''}}" href="{{ route('dashboard_my_addresses') }}" >Adresses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ (\Request::route()->getName() == 'profile.index') ?  'active': ''}}"  href="{{ route('profile.index') }}">Account Details</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('customer.logout') }}">Sign Out</a>
            </li>
        </ul>
    </aside><!-- End .col-lg-3 -->