<?php

namespace App\Http\Middleware;

use App\FoodOrdering\Adapters\FoodSoftOrderingAdapter;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\Repositories\RestaurantRepository;
use Closure;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class EndpointSwitcher
 * @package App\Http\Middleware
 */
class EndpointSwitcher extends BaseApiMiddleware
{
    /**
     * @var RestaurantRepository
     */
    private $restaurantRepository;

    /**
     * EndpointSwitcher constructor.
     * @param RestaurantRepository $restaurantRepository
     */
    public function __construct(RestaurantRepository $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
    }

    /**
     * @param $request
     * @param Closure $next
     * @return JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $restaurantId = $request->route('restaurant');

        if ($restaurantId && $restaurantUrl = $this->restaurantRepository->queryIntegrationInternalUrl($restaurantId)) {
            $centreConfig = $request->centre->config;
            $httpClient = new HttpClient([
                'base_uri' => env('ORDERING_PROVIDER_BASE_URI', $restaurantUrl),
                'connect_timeout' => env(
                    'ORDERING_PROVIDER_BASE_TIMEOUT',
                    ($centreConfig['FoodSoft']['timeout'] ?? 5)
                ),
            ]);

            app()->extend('ProviderHTTPClient', static function () use ($httpClient) {
                return $httpClient;
            });
        }

        return $next($request);
    }
}
