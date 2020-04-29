<?php

namespace App\Http\Requests\Api\Order;

use App\Http\Requests\Api\ApiRequest;

/**
 * Class AllRequest
 * @package App\Http\Requests\Api\Order
 */
class AllRequest extends ApiRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->exists;
    }
}
