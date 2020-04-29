<?php

namespace App\Providers;

use App\Adapters\DatabaseOrderingAdapter;
use App\Entities\FoodOrdering\Basket;
use App\FoodOrdering\Adapters\FoodSoftOrderingAdapter;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\FoodOrdering\Suppliers\FoodSoft;
use App\Helpers\AWSObjectStorage;
use App\Observers\BasketObserver;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

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

        $this->delegateFoodOrderingService();
    }

    /**
     * @return void
     */
    private function delegateFoodOrderingService(): void
    {
        $httpClient = new HttpClient([
            'base_uri' => env('ORDERING_BASE_URI'),
            'connect_timeout' => 5,
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

//        $this->app->bind(
//            FoodOrderingInterface::class,
//            static function (Application $app) use ($httpClient) {
//                switch ($app->make('config')->get('services.food-ordering')) {
//                    case 'database':
//                        return new DatabaseOrderingAdapter;
//                    case 'foodsoft':
//                        return FoodSoftOrderingAdapter::class;
//                    default:
//                        throw new \RuntimeException('Unknown food Ordering Service');
//                }
//            }
//        );
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Basket::observe(BasketObserver::class);
    }
}
