<?php

declare(strict_types=1);

namespace App\FoodOrdering\Transformers;

use App\Entities\Address;
use App\Entities\CentreConfig;
use App\Entities\FoodOrdering\Basket;
use App\Entities\LatLong;
use App\Repositories\RestaurantRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RtfHtmlPhp\Document;
use RtfHtmlPhp\Html\HtmlFormatter;

/**
 * Class MyOrdersTransformer
 * @package App\FoodOrdering\Transformers
 */
class MyOrdersTransformer
{
    /**
     * @var string
     */
    protected $resourceName = 'orders';

    protected $config;

    /**
     * @param Collection $data
     * @return array
     */
    public static function transformData(Collection $data): array
    {
        return (new self)->transform($data);
    }

    /**
     * @param Collection $data
     * @return array
     */
    public function transform(Collection $data): array
    {
        $this->config = request('centre')->config ?? [];

        $orders = [];

        $data->map(static function ($order, $key) use (&$orders) {
            $orders[$key] = OrderTransformer::transformData($order);
            return true;
        });

        return $orders;
    }
}
