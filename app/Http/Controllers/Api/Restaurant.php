<?php

namespace App\Http\Controllers\Api;

use App\Entities\Local;
use App\Entities\Intergration\System;
use App\Entities\Intergration\SystemField;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\FoodOrdering\Transformers\RestaurantTransformer;
use App\FoodOrdering\Suppliers\FoodSoft;
use App\Http\Resources\FoodOrdering\RestaurantCategories;
use App\Repositories\RestaurantRepository;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class Restaurant
 * @package App\Http\Controllers\Api
 */
class Restaurant extends ApiController
{
    /**
     * @var FoodOrderingInterface
     */
    protected $foodOrdering;

    /**
     * @var RestaurantRepository
     */
    protected $restaurantRepository;

    /**
     * Restaurant constructor.
     * @param FoodOrderingInterface $foodOrdering
     * @param RestaurantRepository $restaurantRepository
     */
    public function __construct(FoodOrderingInterface $foodOrdering, RestaurantRepository $restaurantRepository)
    {
        $this->foodOrdering = $foodOrdering;
        $this->restaurantRepository = $restaurantRepository;
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->respond(
            $this->foodOrdering
                ->getRestaurants()
                ->toArray()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse
    {
        $api = $this->foodOrdering;
        $restaurantCategories = $api->getRestaurantCategories();

        $categoryProducts = [];
        $restaurantCategories->map(static function ($category) use ($api, &$categoryProducts) {
            $category['products'] = $api->getCategoryProducts((int) $category['POSPRODGRPID']) ?? Collection::make([]);
            $categoryProducts[] = $category;
        });

        $centreConfig = $request->get('centre')->config;

        $restaurant = Local::find($id)->toArray();
        $restaurant['categories'] = $categoryProducts;
        $restaurant['fees'][] = [
            'name' => 'Admin Fees',
            'net' => (int) $centreConfig['admin_fee_net'],
            'tax' => (int) $centreConfig['admin_fee_tax'],
        ];

        return $this->respond(RestaurantTransformer::transformData($restaurant));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function categories($id)
    {
        $categories = $this->foodOrdering->getCategories();

        return new RestaurantCategories($categories->all());
    }
}
