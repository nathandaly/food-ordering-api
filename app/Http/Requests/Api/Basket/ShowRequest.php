<?php

namespace App\Http\Requests\Api\Basket;

use App\Http\Requests\Api\ApiRequest;

/**
 * Class ShowRequest
 * @package App\Http\Requests\Api0§
 */
class ShowRequest extends ApiRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->exists && $this->basket;
    }
}
