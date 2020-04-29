<?php

namespace App\Providers;

use App\Contracts\AWSObjectInterface;
use App\Entities\FoodOrdering\Order;
use App\Helpers\AWSObjectStorage;
use App\Observers\OrderObserver;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(OrderObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            DB::connection()->enableQueryLog();
        }

        $this->app->bind(
            AWSObjectInterface::class,
            AWSObjectStorage::class
        );
    }
}
