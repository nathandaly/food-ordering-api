<?php

declare(strict_types=1);

namespace App\FoodOrdering\Transformers;

use App\Entities\Address;
use App\Entities\CentreConfig;
use App\Entities\FoodOrdering\Basket;
use App\Entities\FoodOrdering\Order;
use App\Entities\LatLong;
use App\Repositories\RestaurantRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RtfHtmlPhp\Document;
use RtfHtmlPhp\Html\HtmlFormatter;

/**
 * Class OrderTransformer
 * @package App\FoodOrdering\Transformers
 */
class OrderTransformer
{
    /**
     * @var string
     */
    protected $resourceName = 'orders';

    protected $config;

    /**
     * @param Order $order
     * @return array
     */
    public static function transformData(Order $order): array
    {
        return (new self)->transform($order);
    }

    /**
     * @param Order $order
     * @return array
     */
    public function transform(Order $order): array
    {
        $this->config = request('centre')->config ?? [];
        $transformedOrder = [];

        if (!$order->basket_id) {
            return [];
        }

        if (!$basket = Basket::withTrashed()->find($order->basket_id)) {
            return [];
        }

        $local = $basket->local ?? [];
        $basket->local_id = $local->id;
        unset($basket->local);

        $transformedOrder['order_ref'] = $order->order_ref;
        $transformedOrder['created_at'] = Carbon::parse($order->created_at)->format('Y-m-d H:i:s');
        $transformedOrder['updated_at'] = Carbon::parse($order->updated_at)->format('Y-m-d H:i:s');
        $transformedOrder['status'] = $order->status;
        $transformedOrder['disputed'] = $order->disputed ?? false;
        $transformedOrder['store'] = [
            'id' => $local->id,
            'name' => $local->name,
            'address' => (new Address())->fill([
                'unitNumber' => $local->address1,
                'line1' => $local->address2,
                'line2' => $local->address3,
                'town' => $local->address4,
                'postcode' => $local->postcode,
                'phone' => $local->phone,
                'location' => new LatLong(0, 0),
            ])
        ];

        $order->meta_data = (is_string($order->meta_data))
            ? json_decode($order->meta_data, true)
            : $order->meta_data;

        $transformedOrder['totals'] = $order->getTotals();
        $transformedOrder['delivery'] = $order->meta_data['delivery'] ?? null;
        $transformedOrder['deliveryAddress'] = $order->meta_data['deliveryAddress'] ?? null;
        $transformedOrder['discount'] = $order->meta_data['discount'] ?? null;
        $transformedOrder['totals_discount'] = $order->meta_data['totals_discount'] ?? null;
        $transformedOrder['orderTotal'] = $order->meta_data['orderTotal'] ?? null;
        $transformedOrder['meta_data'] = $order->meta_data ?? null;
        $transformedOrder['entity'] = $basket;

        return $transformedOrder;
    }
}
