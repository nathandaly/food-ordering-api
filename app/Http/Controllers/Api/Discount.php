<?php

namespace App\Http\Controllers\Api;

use App\Entities\FoodOrdering\Discount as DiscountEntity;
use App\Exceptions\RepositoryException;
use App\FoodOrdering\Transformers\MyOrdersTransformer;
use App\Http\Requests\Api\Order\AllRequest;
use App\Entities\FoodOrdering\Order as OrderEntity;
use App\Entities\FoodOrdering\Basket as BasketEntity;
use App\Http\Requests\Api\Order\ShowRequest;
use App\Http\Resources\FoodOrdering\Order as OrderResource;
use App\Http\Requests\Api\Order\ProcessOrderRequest;
use App\Repositories\DiscountRepository;
use App\Services\DiscountService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class Discount
 * @package App\Http\Controllers\Api
 */
class Discount extends ApiController
{
    /**
     * @var DiscountRepository
     */
    protected $discountRepository;

    /**
     * @var DiscountService
     */
    protected $discountService;

    /**
     * Discount constructor.
     * @param DiscountRepository $discountRepository
     * @param DiscountService $discountService
     */
    public function __construct(DiscountRepository $discountRepository, DiscountService $discountService)
    {
        $this->discountRepository = $discountRepository;
        $this->discountService = $discountService;
    }

    /**
     * @param Request $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        try {
            $discounts = $this->discountRepository->fetchAllDiscounts(
                $request->get('centreid')
            );
        } catch (RepositoryException | BindingResolutionException $e) {
            return $this->respondError($e->getMessage());
        }

        return $this->respondSuccess($discounts->toArray());
    }

    /**
     * @param Request $request
     * @param String $code
     * @return JsonResponse
     */
    public function show(Request $request, String $code)
    {
        try {
            $discount = $this->discountRepository->fetchDiscount(
                $code,
                (int) $request->get('centreid')
            );

            if ($discount === null) {
                return $this->respondNotFound();
            }

        } catch (RepositoryException | BindingResolutionException $e) {
            return $this->respondError($e->getMessage());
        }

        return $this->respondSuccess($discount->toArray());
    }
}
