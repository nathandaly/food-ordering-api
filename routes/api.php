<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware(['data.api'])->group(static function () {
    if (PHP_VERSION_ID < 70200) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo json_encode(
            [
                'message' => 'PHP version must be 7.2 or higher (current version ID is '
                    . PHP_MAJOR_VERSION
                    . '.'
                    . PHP_MINOR_VERSION
                    .').'
            ]
        );
        exit;
    }

    Route::resource('restaurant', 'Api\Restaurant')
        ->only([
            'index',
            'show',
        ]);
    Route::get('/restaurant/categories/{id}', 'Api\Restaurant@categories')
        ->name('restaurant.categories');

    Route::get('/basket/{local}', 'Api\Basket@index')
        ->name('basket.index');

    Route::get('/basket/clear/{local}', 'Api\Basket@clearBaskets')
        ->name('basket.clear');

    Route::get('basket/{basket}/single', 'Api\Basket@show')
        ->name('basket.show');

    Route::resource('basket', 'Api\Basket')
        ->except([
            'index',
            'edit',
        ]);

    Route::post('/basket/{local}', 'Api\Basket@store')
        ->name('basket.store');

    Route::put('/basket', 'Api\Basket@update')
        ->name('basket.update');

    Route::resource('order', 'Api\Order');

    Route::post('order', 'Api\Order@checkout')
        ->name('order.checkout');

    Route::get('order/{order}/single', 'Api\Order@show')
        ->name('order.show');

    Route::get('order/basket/{basket}', 'Api\Order@showFromBasket')
        ->name('order.show.basket');

    Route::post('order/{order}/complete', 'Api\Order@complete')
        ->name('order.complete');

    Route::get('order/{order}/dispute', 'Api\Order@dispute')
        ->name('order.dispute');

    Route::get('order/{order}/status', 'Api\Order@status')
        ->name('order.status');

    Route::get('order/{basket}/unlink', 'Api\Order@unlinkFromBasket')
        ->name('order.unlink.basket');

    Route::resource('discount', 'Api\Discount')
        ->except([
            'edit',
            'show',
        ]);

    Route::get('discount/{discount}/single', 'Api\Discount@show')
        ->name('discount.show');

});
