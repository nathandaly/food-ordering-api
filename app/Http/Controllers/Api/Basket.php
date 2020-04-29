<?php

namespace App\Http\Controllers\Api;

use App\Entities\Local;
use App\Exceptions\BasketNotFound;
use App\Exceptions\BasketStoreException;
use App\Http\Requests\Api\Basket\DestroyRequest;
use App\Http\Requests\Api\Basket\ShowRequest;
use App\Http\Requests\Api\Basket\StoreRequest;
use App\Http\Requests\Api\Basket\ClearRequest;
use App\Http\Requests\Api\Basket\AllRequest;
use App\Http\Requests\Api\Basket\UpdateRequest;
use App\Http\Resources\FoodOrdering\Basket as BasketResource;
use App\Entities\FoodOrdering\Basket as BasketEntity;
use App\Services\BasketService;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Class Basket
 *
 * @package App\Http\Controllers\Api
 */
class Basket extends ApiController
{
    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * Basket constructor.
     * @param BasketService $basketService
     */
    public function __construct(BasketService $basketService)
    {
        $this->basketService = $basketService;
    }

    /**
     * @param AllRequest $request
     * @param Local $local
     * @return BasketResource|JsonResponse
     */
    public function index(AllRequest $request, Local $local)
    {
        try {
            $basket = $this->basketService
                ->getActiveBasket(
                    Auth::user()->id,
                    (int) $request->get('centreid'),
                    $local->id,
                    (bool) $request->get('with_trashed')
                )
            ;
        } catch (BasketNotFound $e) {
            return $this->respondNotFound($e->getMessage());
        }

        return new BasketResource($basket->toArray());
    }

    /**
     * @param StoreRequest $request
     * @param Local $local
     * @return JsonResponse
     */
    public function store(StoreRequest $request, Local $local): JsonResponse
    {
        try {
            $basket = $this->basketService->snapshotBasket(
                $request->input('uuid'),
                Auth::user()->id,
                $local->id,
                (int) $request->input('centreid'),
                $request->input('basket')
            );
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                return $this->respondError(
                    'Basket WITH uuid \'' . $request->input('uuid') . '\' already exists.',
                    JsonResponse::HTTP_CONFLICT
                );
            }

            return $this->respondError($e->getMessage());
        } catch (BasketStoreException | InvalidUuidStringException $e) {
            return $this->respondError($e->getMessage());
        }

        return $this->respondCreated($basket);
    }

    /**
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(UpdateRequest $request): JsonResponse
    {
        try {
            $uuid = $request->input('uuid');
            $basket = $this->basketService
                ->updateSnapshot(
                    $uuid,
                    $request->input('basket')
                )
            ;
        } catch (BasketStoreException $e) {
            return $this->respondError($e->getMessage());
        }

        return $this->respondCreated($basket);
    }

    /**
     * Return JSON for a single basket.
     *
     * @param ShowRequest  $request Form Request
     * @param BasketEntity $basket  Basket Model
     *
     * @return BasketResource|JsonResponse
     */
    public function show(ShowRequest $request, BasketEntity $basket)
    {
        if ($basket->trashed() && !(bool) $request->get('with_trashed')) {
            return $this->respondNotFound();
        }

        return new BasketResource($basket);
    }

    /**
     * Destroy basket entity by uuid.
     *
     * @param DestroyRequest $request Form Request
     * @param BasketEntity   $basket  Basket Model
     *
     * @throws Exception
     * @return JsonResponse
     */
    public function destroy(DestroyRequest $request, BasketEntity $basket): JsonResponse
    {
        $basket->delete();

        return $this->respondSuccess();
    }

    /**
     * @param ClearRequest $request
     * @param Local $local
     * @return JsonResponse
     */
    public function clearBaskets(ClearRequest $request, Local $local): JsonResponse
    {
        $this->basketService
            ->getBasketRepository()
            ->clearUserBaskets(
                Auth::user()->id,
                (int) $request->input('centreid'),
                $local->id
            )
        ;

        return $this->respondSuccess();
    }
}
