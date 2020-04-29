<?php

namespace App\Repositories;

use App\Entities\FoodOrdering\Basket;
use App\Exceptions\BasketStoreException;
use Ramsey\Uuid\Uuid;

/**
 * Class BasketRepository
 * @package App\Repositories
 */
class BasketRepository extends Repository
{
    /**
     * @param int $profileId
     * @param int $centreId
     * @param int $localId
     * @param bool $withTrashed
     * @return mixed
     */
    public function fetchCurrentActiveBasket(
        int $profileId,
        int $centreId,
        int $localId,
        bool $withTrashed = false
    ): ?Basket  {
        $basketQuery = Basket::query();

        if ($withTrashed) {
            $basketQuery = Basket::withTrashed()->newQuery();
        }

        return $basketQuery->where([
            ['profile_id', '=', $profileId],
            ['centre_id', '=', $centreId],
            ['local_id', '=', $localId],
            ['status', '=', Basket::STATUS_CREATED],
            ])
            ->latest('updated_at')
            ->first();
    }

    /**
     * @param Uuid $uuid
     * @param array $fields
     * @return Basket|null
     */
    public function store(Uuid $uuid, array $fields): ?Basket
    {
        $internal = [
            'uuid' => $uuid->toString(),
            'status' => Basket::STATUS_CREATED,
        ];

        return Basket::create(array_merge($internal, $fields));
    }

    /**
     * @param string $uuid
     * @param array $basketInput
     * @return Basket|null
     * @throws BasketStoreException
     */
    public function update(string $uuid, array $basketInput): ?Basket
    {
        $storeException = new BasketStoreException(
            'Basket with uuid \'' . $uuid . '\' failed to persist.'
        );

        if ($basket = Basket::withTrashed()->where('uuid', $uuid)->firstOrFail()) {
            if ($basket->trashed()) {
                $this->clearUserBaskets(
                    $basket->profileId,
                    $basket->centreId,
                    $basket->localId
                );
                $basket->restore();
            }

            $basket->basket = json_encode($basketInput);

            if (!$basket->save()) {
                throw $storeException;
            }
        }

        return $basket;
    }

    /**
     * @param int $profileId
     * @param int $centreId
     * @param int $localId
     * @return void
     */
    public function clearUserBaskets(int $profileId, int $centreId, int $localId): void
    {
        $query = $this->model->newQuery();

        $baskets = $query
            ->newQuery()
            ->where([
                ['profile_id', '=', $profileId],
                ['centre_id', '=', $centreId],
                ['local_id', '=', $localId],
                ['status', '=', Basket::STATUS_CREATED],
                ])
            ->get()
        ;

        $query->whereIn('id', $baskets->pluck('id'))->delete();
    }

    /**
     * @param string $uuid
     * @return Basket|null
     */
    public function getTrashedBasketByUuid(string $uuid): ?Basket
    {
        return Basket::withTrashed()->where('uuid', $uuid)->first();
    }

    /**
     * @return string
     */
    public function modelClass(): string
    {
        return Basket::class;
    }
}
