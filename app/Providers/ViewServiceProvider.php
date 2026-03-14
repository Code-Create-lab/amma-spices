<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Support\Facades\View;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
            View::composer('*', function ($view) {
                $view->with('nav_cats', Coupon::where('show_on_web', 1)->orderBy('coupon_id')->first());
            });

            View::composer('frontend.layouts.app', function ($view) {
                $view->with('mobileNavCategories', Category::where('parent', 0)
                    ->where('status', 1)
                    ->with(['sub_categories' => function ($q) {
                        $q->where('status', 1)->with(['sub_categories' => function ($q2) {
                            $q2->where('status', 1);
                        }, 'products' => function ($q2) {
                            $q2->where('is_deleted', 0);
                        }]);
                    }, 'products' => function ($q) {
                        $q->where('is_deleted', 0);
                    }])
                    ->get());
            });
    }
}
