<?php

namespace App\Http\Requests\Api\Order;

use App\Entities\FoodOrdering\Basket;
use App\Entities\Payment\PaymentRequest;
use App\Http\Requests\Api\ApiRequest;

/**
 * Class ProcessOrderRequest
 * @package App\Http\Requests\Api\Order
 */
class ProcessOrderRequest extends ApiRequest
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
        return [
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
     * @return void
     */
    protected function passedValidation(): void
    {
        $this->basket = Basket::withTrashed()
            ->where(['uuid' => $this->input('basketId')])
            ->latest('updated_at')
            ->first();

        $this->paymentRequest = (new PaymentRequest())->fill($this->input('paymentRequest'));
    }
}
