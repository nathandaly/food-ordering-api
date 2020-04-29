<?php

namespace App\Observers;

use App\Entities\FoodOrdering\Basket;
use Illuminate\Support\Collection;

/**
 * Class BasketObserver
 * @package App\Observers
 */
class BasketObserver
{
    public function retrieved(Basket $basket)
    {
        if (empty($basket->basket)) {
            $basket->basket = Collection::make([]);
        }
    }
}
