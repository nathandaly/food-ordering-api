<?php

namespace App\Http\Controllers\Api;

use App\Entities\FoodOrdering\DiscountsApplied;
use App\Entities\Payment\PaymentRequest;
use App\FoodOrdering\Transformers\MyOrdersTransformer;
use App\FoodOrdering\Transformers\OrderTransformer;
use App\Http\Requests\Api\Order\AllRequest;
use App\Entities\FoodOrdering\Order as OrderEntity;
use App\Entities\FoodOrdering\Basket as BasketEntity;
use App\Entities\FoodOrdering\Discount as DiscountEntity;
use App\Http\Requests\Api\Order\OrderChangeRequest;
use App\Http\Requests\Api\Order\ShowRequest;
use App\Http\Resources\FoodOrdering\Order as OrderResource;
use App\Http\Requests\Api\Order\ProcessOrderRequest;
use App\Repositories\BasketRepository;
use App\Repositories\OrderRepository;
use App\Repositories\RestaurantRepository;
use App\Services\OrderService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class Order
 * @package App\Http\Controllers\Api
 */
class Order extends ApiController
{
    /**
     * @var BasketRepository
     */
    protected $basketRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var RestaurantRepository
     */
    protected $restaurantRepository;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * Order constructor.
     * @param BasketRepository $basketRepository
     * @param OrderRepository $orderRepository
     * @param RestaurantRepository $restaurantRepository
     * @param OrderService $orderService
     */
    public function __construct(
        BasketRepository $basketRepository,
        OrderRepository $orderRepository,
        RestaurantRepository $restaurantRepository,
        OrderService $orderService
    ) {
        $this->basketRepository = $basketRepository;
        $this->orderRepository = $orderRepository;
        $this->restaurantRepository = $restaurantRepository;
        $this->orderService = $orderService;
    }

    /**
     * @param AllRequest $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function index(AllRequest $request)
    {
        $orders = $this->orderRepository->fetchAllOrders(
            $request->get('profile')->id,
            (int) $request->get('centreid'),
            (bool) $request->get('with_trashed')
        );

        if (!$orders) {
            return $this->respondNotFound('No active associated baskets or orders found.');
        }

        return $this->respond(MyOrdersTransformer::transformData($orders));
    }

    /**
     * Return JSON for a single order.
     *
     * @param ShowRequest  $request Form Request
     * @param OrderEntity $order  Order Model
     *
     * @return OrderResource|JsonResponse
     */
    public function show(ShowRequest $request, OrderEntity $order)
    {
        if (!$order) {
            return $this->respondNotFound();
        }

        if ($order->trashed() && !(bool) $request->get('with_trashed')) {
            return $this->respondNotFound();
        }

        return $this->respond(OrderTransformer::transformData($order));
    }

    /**
     * Return JSON for a single order.
     *
     * @param ShowRequest  $request Form Request
     * @param BasketEntity $basket  Basket Model
     *
     * @return OrderResource|JsonResponse
     */
    public function showFromBasket(ShowRequest $request, BasketEntity $basket)
    {
        if (!$basket) {
            return $this->respondNotFound();
        }

        if ($basket->trashed() && !(bool) $request->get('with_trashed')) {
            return $this->respondNotFound();
        }

        if (!$order = OrderEntity::where('basket_id', $basket->id)->first()) {
            return $this->respondNotFound();
        }

        if ($order->trashed() && !(bool) $request->get('with_trashed')) {
            return $this->respondNotFound();
        }

        return $this->respond(OrderTransformer::transformData($order));
    }

    /**
     * @param ProcessOrderRequest $request
     * @return JsonResponse
     */
    public function checkout(ProcessOrderRequest $request): JsonResponse
    {
        if (!$basket = $request->getBasket()) {
            return $this->respondError(BasketEntity::class . ' is null.');
        }

        if (!$paymentRequest = $request->getPaymentRequest()) {
            return $this->respondError(PaymentRequest::class . ' is null.');
        }

        $config = $request->get('centre')->config ?? [];

        if ($basket->trashed()) {
            return $this->respondNotFound('Basket with UUID: ' . $basket->uuid . ' has been deleted.');
        }

        try {
            // Save initial order information from apps.
            $adminFee = $config['admin_fee_net'] + $config['admin_fee_tax'];
            $newOrder = OrderEntity::create([
                'basket_id' => $basket->id,
                'order_ref' => 'PA-00',
                'status' => OrderEntity::STATUS_EDITING,
                'total_payment_price' => $paymentRequest->valueGross,
                'net_cost' => $paymentRequest->valueNet,
                'tax_cost' => $paymentRequest->valueNetTax,
                'gross_cost' => $paymentRequest->valueNet + $paymentRequest->valueNetTax,
                'net_admin_fee' => $config['admin_fee_net'],
                'tax_admin_fee' => $config['admin_fee_tax'],
                'gross_admin_fee' => $adminFee,
                'iso_currency_code' => 'PLN',
            ]);

            /** @var Client $paymentClient */
            $paymentClient = app()->make('PaymentClient');
            $response = $paymentClient->request('POST', 'order/create', [
                'form_params' => $paymentRequest->toArray()
                    + ['data' => $request->get('raw_data')],
            ]);

            if (!in_array(($statusCode = $response->getStatusCode()), [200, 201], true)) {
                throw new \RuntimeException('Payment provider returned ' . $statusCode);
            }

            $result = json_decode((string) $response->getBody(), true);
            $newOrder->update([
                'status' => OrderEntity::STATUS_PAYMENT_PENDING,
                'order_ref' => $result['orderRef'],
            ]);
            $newOrder->setMetadata([
                'payment_service' => $result
            ]);
        } catch (ClientException $e) {
            Log::error('PAYMENT ERROR: ' . $e->getMessage());
            return $this->respondError('A payment provider exception occurred.');
        } catch (BindingResolutionException $e) {
            Log::error('PAYMENT ERROR: ' . $e->getMessage());
            return $this->respondError('Payment Service Provider not registered.');
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR;
            Log::error('PAYMENT ERROR: ' . $message);

            if ((int) $e->getCode() === 23000) {
                $message = 'That order is already associated with basket UUID: ' . $basket->uuid;
                $code = JsonResponse::HTTP_CONFLICT;
            }

            return $this->respondError($message, $code);
        }

        return $this->respond(OrderTransformer::transformData($newOrder));
    }

    /**
     * @param OrderChangeRequest $request
     * @param OrderEntity $order
     * @return JsonResponse
     */
    public function update(OrderChangeRequest $request, OrderEntity $order): JsonResponse
    {
        if ($order->status === OrderEntity::STATUS_COMPLETE) {
            return $this->respondError('Cannot update, order already complete');
        }

        try {
            if (!$basket = $request->getBasket()) {
                return $this->respondError(BasketEntity::class . ' is null.');
            }

            if (!$paymentRequest = $request->getPaymentRequest()) {
                return $this->respondError(PaymentRequest::class . ' is null.');
            }

            $discountRequest = $request->getDiscount();

            if (
                $discountRequest
                && $discount = DiscountEntity::where([
                    'centre_id' => $request->get('centre')->id,
                    'code' => $discountRequest['code'],
                ])->first()
            ) {
                $order->setMetadata([
                    'discount' => DiscountEntity::with('items')->find($discount->id)->toArray(),
                ]);
            } else {
                return $this->respondError('Discount code does not exist');
            }

            $saved = $order->update([
                'basket_id' => $basket->id,
                'order_ref' => $order->order_ref,
                'status' => OrderEntity::STATUS_EDITING,
                'total_payment_price' => $paymentRequest->valueGross,
                'net_cost' => $paymentRequest->valueNet,
                'tax_cost' => $paymentRequest->valueNetTax,
                'gross_cost' => $paymentRequest->valueNet + $paymentRequest->valueNetTax,
                'disputed' => $request->input('disputed'),
            ]);

            $order->setMetadata([
                'delivery' => $request->getDelivery(),
                'deliveryAddress' => $request->get('deliveryAddress'),
                'orderTotal' => $request->get('orderTotal'),
                'totals_discount' => $request->get('totals_discount'),
            ]);

            if (!$saved) {
                return $this->respondError(
                    'Failed to update order',
                    JsonResponse::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            /** @var Client $paymentClient */
            $paymentClient = app()->make('PaymentClient');
            $paymentRequest = $paymentRequest->toArray();
            $response = $paymentClient->request('POST', 'order/update', [
                'form_params' => $paymentRequest + [
                    'data' => $request->get('raw_data'),
                    'orderRef' => $order->order_ref
                ],
            ]);

            if (!in_array(($statusCode = $response->getStatusCode()), [200, 201], true)) {
                throw new \RuntimeException('Payment provider returned ' . $statusCode);
            }

            $result = json_decode((string)$response->getBody(), true);
            $order->setMetadata([
                'payment_service' => $result
            ]);
            $order->save();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }

        return $this->respond(OrderTransformer::transformData($order));
    }

    /**
     * @param OrderChangeRequest $request
     * @param OrderEntity $order
     * @return JsonResponse
     */
    public function complete(OrderChangeRequest $request, OrderEntity $order): JsonResponse
    {
        $transformedOrder = OrderTransformer::transformData($order);

        $order->fill([
            'status' => OrderEntity::STATUS_COMPLETE,
        ]);
        $order->setMetadata([
            'delivery' => $request->getDelivery(),
            'deliveryAddress' => $request->get('deliveryAddress'),
            'orderTotal' => $request->get('orderTotal'),
            'totals_discount' => $request->get('totals_discount'),
        ]);
        $order->save();

        if (!empty($transformedOrder['discount'])) {

            $discount = DiscountEntity::where([
                'centre_id' => $request->get('centre')->id,
                'code' => $transformedOrder['discount']['code'],
            ])->first();

            if ($discount) {
                DiscountsApplied::updateOrCreate([
                    'discount_id' => $discount['id'],
                    'order_id' => $order['id'],
                    'profile_id' => $request->get('profile')->id,
                ]);
            } else {
                return $this->respondError('Discount code does not exist');
            }
        }

        return $this->respond($transformedOrder);
    }

    /**
     * @param OrderEntity $order
     * @return JsonResponse
     */
    public function dispute(OrderEntity $order): JsonResponse
    {
        $order->disputed = true;
        $order->save();

        return $this->respond(OrderTransformer::transformData($order));
    }

    /**
     * @param OrderEntity $order
     * @return JsonResponse
     */
    public function status(OrderEntity $order): JsonResponse
    {
        return $this->respondSuccess(
            $this->orderService->getStatus($order)
        );
    }

    /**
     * @param Request $request
     * @param BasketEntity $basket
     * @return OrderResource|JsonResponse
     */
    public function unlinkFromBasket(Request $request, BasketEntity $basket): ?JsonResponse
    {
        if (!$basket) {
            return $this->respondNotFound();
        }

        if (!$order = OrderEntity::where('basket_id', $basket->id)->first()) {
            return $this->respondNotFound();
        }

        if ($order->trashed() && !(bool) $request->get('with_trashed')) {
            return $this->respondNotFound();
        }

        $order->update([
            'basket_id' => null,
            'status' => OrderEntity::STATUS_ABANDONED
        ]);

        $order->delete();

        return $this->respond(OrderTransformer::transformData($order));
    }

    /**
     * @param AllRequest $request
     * @param OrderEntity $order
     * @return OrderResource|JsonResponse
     */
    public function destroy(AllRequest $request, OrderEntity $order)
    {
        try {
            $order->update([
                'basket_id' => null,
                'status' => OrderEntity::STATUS_USER_DELETED
            ]);
            $order->delete();
        } catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }

        return new OrderResource($order);
    }
}
