<?php

namespace App\Observers;

use App\Entities\Centre;
use App\Entities\FoodOrdering\Basket;
use App\Entities\FoodOrdering\Order;
use App\Mail\OrderComplete;
use App\Repositories\DeviceTokenRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Class OrderObserver
 * @package App\Observers
 */
class OrderObserver
{
    /**
     * @var DeviceTokenRepository
     */
    protected $deviceTokenRepository;

    /**
     * OrderObserver constructor.
     * @param DeviceTokenRepository $deviceTokenRepository
     */
    public function __construct(DeviceTokenRepository $deviceTokenRepository)
    {
        $this->deviceTokenRepository = $deviceTokenRepository;
    }

    /**
     * @param Order $order
     */
    public function saving(Order $order): void
    {
        $actionableStatuses = [
            Order::STATUS_PAYMENT_COMPLETE,
            Order::STATUS_PREPARING,
            Order::STATUS_COLLECTION_READY,
            Order::STATUS_DISPATCHED,
            Order::STATUS_COMPLETE,
        ];

        if (
            in_array($order['status'], $actionableStatuses, true)
            && $basket = Basket::with('centre')->find($order->basket_id)
        ) {
            /**
             * Eager load centre.
             * @var Centre
             */
            $centre = $basket->centre;
            $profileTokens = $this->deviceTokenRepository->sortTokens(
                $centre->id,
                $this->deviceTokenRepository->getProfilesTokens([$basket->profile_id])
            );

            $pushLang = $this->pushLang($centre, $order);
            $user = request()->user();
            $user->email = 'neil.edwards@toolboxmarketing.com';

            $payload = [
                'action' => 'food_order_details',
                'title' => $centre->name,
                'message' => $pushLang[$order['status']] ?? '',
                'push_data' => [
                    'local_id' => $basket->local_id ?? 0,
                    'order_ref' => $order->order_ref ?? '', // TODO: Order reference not available at this point.
                ],
                'email_data' => [
                    'user' => $user,
                ],
            ];

            try {
                Mail::to(request()->user())->send(new OrderComplete($payload));
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }

            $this->deviceTokenRepository->sendPush(
                $centre->appid,
                $centre->id,
                $profileTokens,
                $payload
            );
        }
    }

    /**
     * @param Centre $centre
     * @param Order $order
     * @return array
     */
    private function pushLang(Centre $centre, Order $order): array
    {
        $langCode = strtolower(request('data.lang') ?? $centre['language']);

        switch ($langCode) {
            default:
            case 'en':
                $lang = [
                    'PAYMENT_COMPLETE' => "{$order['order_ref']} \r\nYour order has been received and will be processed shortly",
                    'PREPARING' => "{$order['order_ref']} \r\nYour order is currently being prepared",
                    'COLLECTION_READY' => "{$order['order_ref']} \r\nYour order has been completed and ready for collection",
                    'DISPATCHED' => "{$order['order_ref']} \r\nYour order has been completed and is out for delivery",
                    'COMPLETE' => "{$order['order_ref']} \r\nYour order has been marked as completed",
                ];
                break;
            case 'pl':
                $lang = [
                    'PAYMENT_COMPLETE' => "{$order['order_ref']} \r\nTwoje zamówienie zostało przyjęte i będzie wkrótce przygotowywane",
                    'PREPARING' => "{$order['order_ref']} \r\nTwoje zamówienie jest przygotowywane",
                    'COLLECTION_READY' => "{$order['order_ref']} \r\nTwoje zamówienie jest gotowe do odbioru",
                    'DISPATCHED' => "{$order['order_ref']} \r\nGotowe! Twoje zamówienie jest w drodze",
                    'COMPLETE' => "{$order['order_ref']} \r\nDziękujemy! Twoje zamówienia zostało zrealizowane",
                ];
                break;
        }

        return $lang;
    }
}
