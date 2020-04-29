<?php

namespace App\Http\Requests\Api\Basket;

use App\Http\Requests\Api\ApiRequest;
use App\Traits\ValidateLocalParam;

/**
 * Class AllRequest
 * @package App\Http\Requests\Api
 */
class AllRequest extends ApiRequest
{
    use ValidateLocalParam;

    /**
     * @return bool|void
     */
    public function authorize()
    {
        return $this->user()->exists;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'data' => 'required',
            'with_trashed' => 'sometimes|boolean',
        ];
    }
}
