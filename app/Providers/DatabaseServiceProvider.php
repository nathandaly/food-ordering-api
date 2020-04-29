<?php

namespace App\Providers;

use App\Helpers\DatabaseConnection;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        DatabaseConnection::addConnection($apiKey);
        DatabaseConnection::setDefaultConnection($apiKey);
    }
}
