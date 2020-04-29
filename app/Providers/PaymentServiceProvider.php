<?php

namespace App\Providers;

use App\Adapters\DatabaseOrderingAdapter;
use App\Entities\FoodOrdering\Basket;
use App\FoodOrdering\Adapters\FoodSoftOrderingAdapter;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\FoodOrdering\Suppliers\FoodSoft;
use App\Http\Controllers\Api\Order;
use App\Observers\BasketObserver;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

/**
 * Class PaymentServiceProvider
 * @package App\Providers
 */
class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register any payment service.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->instance('PaymentClient', new HttpClient([
            'base_uri' => env('PAYMENT_BASE_URI'),
            'connect_timeout' => 5,
        ]));
    }
}
