<?php

namespace App\Services;

use App\Entities\FoodOrdering\Basket;
use App\Exceptions\BasketNotFound;
use App\Exceptions\BasketStoreException;
use App\Repositories\BasketRepository;
use Illuminate\Support\Str;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;

/**
 * Class BasketService
 * @package App\Services
 */
class BasketService
{
    /**
     * @var BasketRepository
     */
    protected $basketRepository;

    /**
     * BasketService constructor.
     * @param BasketRepository $basketRepository
     */
    public function __construct(BasketRepository $basketRepository)
    {
        $this->basketRepository = $basketRepository;
    }

    /**
     * @return BasketRepository
     */
    public function getBasketRepository(): BasketRepository
    {
        return $this->basketRepository;
    }

    /**
     * @param int $profileId
     * @param int $centreId
     * @param int $localId
     * @param bool $withTrashed
     * @return Basket
     * @throws BasketNotFound
     */
    public function getActiveBasket(int $profileId, int $centreId, int $localId, bool $withTrashed): Basket
    {
        $basket = $this->basketRepository->fetchCurrentActiveBasket(
            $profileId,
            $centreId,
            $localId,
            $withTrashed
        );

        if (!$basket) {
            throw new BasketNotFound(
                'No active associated baskets or orders found.'
            );
        }

        return $basket;
    }

    /**
     * @param string $uuid
     * @param int $profileId
     * @param int $localId
     * @param int $centreId
     * @param array $basketInput
     * @return Basket|null
     * @throws BasketStoreException
     */
    public function snapshotBasket(
        string $uuid,
        int $profileId,
        int $localId,
        int $centreId,
        array $basketInput
    ) :?Basket {
        if (!Str::isUuid($uuid)) {
            throw new InvalidUuidStringException('Not a valid UUID v4.');
        }

        $this->basketRepository->clearUserBaskets($profileId, $centreId, $localId);

        if ($basket = $this->basketRepository->getTrashedBasketByUuid($uuid)) {
            if ($basket->trashed()) {
                $basket->restore();
            }

            $basket->basket = json_encode($basketInput);

            if (!$basket->save()) {
                throw $this->getBasketStoreException($uuid);
            }

            return $basket;
        }

        $basket = $this->basketRepository->store(
            Uuid::fromString($uuid),
            [
                'profile_id' => $profileId,
                'local_id' => $localId,
                'centre_id' => $centreId,
                'basket' => json_encode($basketInput),
            ]
        );

        if (!$basket) {
            throw $this->getBasketStoreException($uuid);
        }

        return $basket;
    }

    /**
     * @param string $uuid
     * @param array $basketInput
     * @return Basket|null
     * @throws BasketStoreException
     */
    public function updateSnapshot(string $uuid, array $basketInput): ?Basket
    {
        if ($basket = $this->basketRepository->getTrashedBasketByUuid($uuid)) {
            if ($basket->trashed()) {
                $this->basketRepository->clearUserBaskets(
                    $basket->profileId,
                    $basket->centreId,
                    $basket->localId
                );
                $basket->restore();
            }

            $basket->basket = json_encode($basketInput);

            if (!$basket->save()) {
                throw $this->getBasketStoreException($uuid);
            }
        }

        return $basket;
    }

    /**
     * @param Basket $basket
     * @return Basket
     */
    public function validateBasket(Basket $basket)
    {
        return $basket;
    }

    /**
     * @param string $uuid
     * @return BasketStoreException
     */
    private function getBasketStoreException(string $uuid): BasketStoreException
    {
        return new BasketStoreException(
            'Basket with uuid \'' . $uuid . '\' failed to persist.'
        );
    }
}
