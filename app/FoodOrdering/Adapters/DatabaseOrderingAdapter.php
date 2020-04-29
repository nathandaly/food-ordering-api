<?php

namespace App\Adapters;

use App\FoodOrdering\Contracts\FoodOrderingInterface;
use Illuminate\Support\Collection;

/**
 * Class DatabaseFoodOrderingAdapter
 * @package App\Adapters
 */
class DatabaseOrderingAdapter
{
    /**
     * @return Collection
     */
    public function getRestaurants(): Collection
    {
        return Collection::make([]);
    }

    /**
     * @param int $restaurantId
     * @return array
     */
    public function getRestaurant(int $restaurantId): array
    {
        return [];
    }

    /**
     * @param int $restaurantId
     * @return object
     */
    public function getMenu(int $restaurantId): object
    {
        return new \stdClass();
    }

    /**
     * @param int $productId
     * @return object
     */
    public function getMenuItem(int $productId): object
    {
        return new \stdClass();
    }
}
