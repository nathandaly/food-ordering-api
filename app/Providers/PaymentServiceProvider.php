<?php

namespace App\Providers;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;

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
