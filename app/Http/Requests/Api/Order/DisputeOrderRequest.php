<?php

namespace App\Http\Requests\Api\Order;

use App\Entities\FoodOrdering\Basket;
use App\Entities\FoodOrdering\OrderTotals;
use App\Http\Requests\Api\ApiRequest;

/**
 * Class DisputeOrderRequest
 * @package App\Http\Requests\Api\Order
 */
class DisputeOrderRequest extends ApiRequest
{
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
            'orderRef' => 'required|string'
        ];
    }
}
