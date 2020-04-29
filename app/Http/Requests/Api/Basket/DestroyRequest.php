<?php

namespace App\Http\Requests\Api\Basket;

use App\Http\Requests\Api\ApiRequest;

/**
 * Class BasketUpdateRequest
 * @package App\Http\Requests\Api
 */
class DestroyRequest extends ApiRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->exists;
    }
}
