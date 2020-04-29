<?php

namespace App\FoodOrdering\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface FoodOrderingInterface
 * @package App\FoodOrdering\Contracts
 */
interface FoodOrderingInterface
{
    /**
     * @return Collection
     */
    public function getRestaurants(): Collection;

    /**
     * @param int $restaurantId
     * @return array
     */
    public function getRestaurant(int $restaurantId): array;

    /**
     * @return Collection
     */
    public function getCategories(): Collection;

    /**
     * @param int $restaurantId
     * @return Collection
     */
    public function getCategoriesByRestaurant(int $restaurantId): Collection;

    /**
     * @param int $categoryId
     * @return Collection
     */
    public function getCategoryProducts(int $categoryId): Collection;

    /**
     * @param int $orderIdentifier
     * @return int
     */
    public function getOrderStatus(int $orderIdentifier): int;
}
