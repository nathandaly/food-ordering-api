<?php

namespace App\Http\Requests\Api\Basket;

use App\Http\Requests\Api\ApiRequest;
use App\Traits\ValidateLocalParam;

/**
 * Class BasketStoreRequest
 * @package App\Http\Requests\Api
 */
class StoreRequest extends ApiRequest
{
    use ValidateLocalParam;

    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->exists;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uuid' => 'bail|required|uuid',
            'basket' => 'required|array',
        ];
    }
}
