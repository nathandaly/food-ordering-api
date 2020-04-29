<?php

namespace App\Http\Middleware;

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
            $httpClient = new HttpClient([
                'base_uri' => $restaurantUrl,
                'connect_timeout' => 5, // TODO: Get this from centre config.
            ]);

            app()->extend('FoodSoft\API', static function ($service, $app) use ($httpClient) {
                return new $service($httpClient);
            });
        }

        return $next($request);
    }
}
