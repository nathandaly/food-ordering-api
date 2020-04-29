<?php

namespace App\Services;

use App\Entities\FoodOrdering\Order;
use App\FoodOrdering\Contracts\FoodOrderingInterface;
use App\Repositories\OrderRepository;

/**
 * Class OrderService
 * @package App\Services
 */
class OrderService
{
    /**
     * @var FoodOrderingInterface
     */
    protected $foodOrdering;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * OrderService constructor.
     * @param FoodOrderingInterface $foodOrdering
     * @param OrderRepository $orderRepository
     */
    public function __construct(FoodOrderingInterface $foodOrdering, OrderRepository $orderRepository)
    {
        $this->foodOrdering = $foodOrdering;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Order $order
     * @return array
     */
    public function getStatus(Order $order): array
    {
        $orderClone = clone $order;
        $orderStatus = $orderClone->status;

        try {
            $orderStatus = $this->foodOrdering->getOrderStatus(0);
        } catch (\Exception $e) {}

        return [
            'status' => $orderStatus,
            'updated' => $orderClone->updated_at,
        ];
    }
}
