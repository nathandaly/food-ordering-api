<?php

namespace App\Repositories;

use App\Entities\FoodOrdering\Basket;
use App\Entities\FoodOrdering\Order;
use App\Exceptions\BasketStoreException;
use Illuminate\Support\Collection;

/**
 * Class OrderRepository
 * @package App\Repositories
 */
class OrderRepository extends Repository
{
    /**
     * @param int $profileId
     * @param int $centreId
     * @param bool $withTrashed
     * @return Collection|null
     */
    public function fetchAllOrders(int $profileId, int $centreId, bool $withTrashed = false): ?Collection  {
        $orderQuery = Order::query();

        if ($withTrashed) {
            $orderQuery = Order::withTrashed()->newQuery();
        }

        $baskets = Basket::withTrashed()
            ->where([
                ['profile_id', '=', $profileId],
                ['centre_id', '=', $centreId],
            ])
            ->get();

        return $orderQuery
            ->whereIn('basket_id', $baskets->pluck('id'))
            ->latest('updated_at')
            ->get();
    }

    /**
     * @return string
     */
    public function modelClass(): string
    {
        return Order::class;
    }
}
