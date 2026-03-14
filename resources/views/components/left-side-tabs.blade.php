<div class="left-side-tabs">
    <div class="dashboard-left-links">
        <a href="{{ route('profile.index') }}" class="user-item {{ request()->is('profile') ? 'active' : '' }}"><i
                class="uil uil-apps"></i>My Profile</a>
        <a href="{{ route('customer.orders.index') }}" class="user-item {{ request()->is('orders') ? 'active' : '' }}"><i class="uil uil-box"></i>My
            Orders</a>
        {{-- <a href="dashboard_my_rewards.html" class="user-item"><i class="uil uil-gift"></i>My
            Rewards</a>
        <a href="dashboard_my_wallet.html" class="user-item"><i class="uil uil-wallet"></i>My
            Wallet</a> --}}
        <a href="{{ route('wishlist') }}" class="user-item {{ request()->is('wishlist') ? 'active' : '' }}"><i class="uil uil-heart"></i>Shopping
            Wishlist</a>
        <a href="{{ route('dashboard_my_addresses') }}" class="user-item {{ request()->is('address/*') ? 'active' : '' }}"><i
                class="uil uil-location-point"></i>My Address</a>
        <a href="{{ route('customer.logout') }}" class="user-item"><i class="uil uil-exit"></i>Logout</a>
    </div>
</div>