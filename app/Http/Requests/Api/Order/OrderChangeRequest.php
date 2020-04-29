<?php

namespace App\Http\Requests\Api\Order;

use App\Entities\FoodOrdering\Basket;
use App\Entities\FoodOrdering\Delivery;
use App\Entities\FoodOrdering\Discount;
use App\Entities\FoodOrdering\DiscountItems;
use App\Entities\FoodOrdering\Fee;
use App\Entities\Payment\PaymentRequest;
use App\Http\Requests\Api\ApiRequest;
use Illuminate\Support\Collection;

/**
 * Class OrderChangeRequest
 * @package App\Http\Requests\Api\Order
 */
class OrderChangeRequest extends ApiRequest
{
    /**
     * @var Basket
     */
    protected $basket;

    /**
     * @var PaymentRequest
     */
    protected $paymentRequest;

    /**
     * @var Delivery
     */
    protected $delivery;

    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->exists;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'discount_code' => 'string',
            'delivery.type' => 'required|string',
            'delivery.fee.type' => 'required|string',
            'delivery.fee.value' => 'required|int',
            'delivery.fee.target' => 'required|string',
            'basketId' => 'required|uuid|exists:fo_basket,uuid',
            'paymentRequest.valueFeeTax' => 'required|int',
            'paymentRequest.localIdPaid' => 'required|int',
            'paymentRequest.valueFee' => 'required|int',
            'paymentRequest.country' => 'required|string',
            'paymentRequest.valueNet' => 'required|int',
            'paymentRequest.valueNetTax' => 'required|int',
            'paymentRequest.valueGross' => 'required|int',
            'paymentRequest.currency' => 'required|string',
            'paymentRequest.description' => 'required|string',
            'paymentRequest.categoryId' => 'int',
        ];

        $deliveryAddressRules = [
            'deliveryAddress.name' => 'string',
            'deliveryAddress.line1' => 'string',
            'deliveryAddress.city' => 'string',
            'deliveryAddress.state' => 'string',
            'deliveryAddress.country' => 'string',
            'deliveryAddress.postal_code' => 'string',
            'deliveryAddress.phone' => 'string',
        ];

        if ($this->input('delivery.type') === 'deliver') {
          $rules = array_merge($rules, $deliveryAddressRules);
        }

        return $rules;
    }

    /**
     * @return Basket
     */
    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    /**
     * @return PaymentRequest
     */
    public function getPaymentRequest(): ?PaymentRequest
    {
        return $this->paymentRequest;
    }

    /**
     * @return Delivery
     */
    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    /**
     * @return Delivery
     */
    public function getDeliveryAddress(): ?Delivery
    {
        return $this->delivery;
    }

    /**
     * @return Discount|null
     */
    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    /**
     * @return void
     */
    protected function passedValidation(): void
    {
        $this->basket = Basket::withTrashed()
            ->where(['uuid' => $this->input('basketId')])
            ->latest('updated_at')
            ->first();

        $this->paymentRequest = (new PaymentRequest())->fill($this->input('paymentRequest'));

        $this->delivery = (new Delivery())->fill([
           'type' => $this->input('delivery.type'),
           'fee' => (new Fee)->fill($this->input('delivery.fee')),
        ]);

        if ($discountRequest = $this->input('discount')) {
            $items = Collection::make([]);
            foreach ($this->input('discount.items') as $item) {
                $items->push(new DiscountItems($item));
            }
            $discountRequest['items'] = $items;
            $this->discount = new Discount($discountRequest);
        }
    }
}
