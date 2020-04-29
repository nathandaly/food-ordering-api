<?php

namespace App\Http\Requests\Api\Order;

use App\Http\Requests\Api\ApiRequest;

/**
 * Class ShowRequest
 * @package App\Http\Requests\Api
 */
class ShowRequest extends ApiRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->exists;
    }
}
