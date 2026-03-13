<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\Wishlist;
use App\Policies\CartPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserAddressPolicy;
use App\Policies\VendorPolicy;
use App\Policies\WishlistPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Cart::class, CartPolicy::class);
        Gate::policy(UserAddress::class, UserAddressPolicy::class);
        Gate::policy(Vendor::class, VendorPolicy::class);
        Gate::policy(Wishlist::class, WishlistPolicy::class);

        Gate::define('viewPulse', function ($user) {
            return $user->hasRole('admin');
        });
    }
}
