<?php

namespace App\Repositories;

use App\Entities\FoodOrdering\Discount;
use App\Exceptions\RepositoryException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

/**
 * Class DiscountRepository
 * @package App\Repositories
 */
class DiscountRepository extends Repository
{
    /**
     * @param int $centreId
     * @param int|null $localId
     * @return Collection
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function fetchAllDiscounts(int $centreId): Collection
    {
        $discountModel = $this->makeModel();

        return $discountModel::query()->with('items')->where([
            ['centre_id', '=', $centreId],
        ])->get();
    }

    /**
     * @param string $discountCode
     * @param int $centreId
     * @return Discount|null
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function fetchDiscount(string $discountCode, int $centreId): ?Discount
    {
        $discountModel = $this->makeModel();

        return $discountModel::query()->with('items')->where([
            ['code', '=', $discountCode],
            ['centre_id', '=', $centreId],
        ])->first();
    }

    /**
     * @return string
     */
    public function modelClass(): string
    {
        return Discount::class;
    }
}
