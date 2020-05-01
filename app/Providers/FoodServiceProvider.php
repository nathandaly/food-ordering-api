<?php

namespace App\Providers;

use App\Adapters\DatabaseOrderingAdapter;
use App\Entities\FoodOrdering\Basket;
use App\FoodOrdering\Adapters\FoodSoftOrderingAdapter;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\FoodOrdering\Suppliers\FoodSoft;
use App\Observers\BasketObserver;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

/**
 * Class FoodServiceProvider
 * @package App\Providers
 */
class FoodServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            FoodOrderingInterface::class,
            DatabaseOrderingAdapter::class
        );

        $this->app->bind(
            FoodOrderingInterface::class,
            FoodSoftOrderingAdapter::class
        );

        $this->app->instance('ProviderHTTPClient', new HttpClient([
            'base_uri' => env('ORDERING_PROVIDER_BASE_URI'),
            'connect_timeout' => env('ORDERING_PROVIDER_BASE_TIMEOUT', 5),
        ]));

        $this->delegateFoodOrderingService();
    }

    /**
     * @return void
     */
    private function delegateFoodOrderingService(): void
    {
        $httpClient = new HttpClient([
            'base_uri' => env('ORDERING_PROVIDER_BASE_URI'),
            'connect_timeout' => env('ORDERING_PROVIDER_BASE_TIMEOUT', 5),
        ]);

        $this->app->instance('FoodSoft\API', new FoodSoft($httpClient));

        $implementation = null;

        switch (config('services.food-ordering')) {
            case 'database':
                $implementation = DatabaseOrderingAdapter::class;
                break;
            case 'foodsoft':
                $implementation = FoodSoftOrderingAdapter::class;
                break;
            default:
                throw new \RuntimeException('Unknown food Ordering Service');
        }

        $this->app->bind(FoodOrderingInterface::class, $implementation);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Basket::observe(BasketObserver::class);
    }
}
