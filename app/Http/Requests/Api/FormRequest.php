<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

/**
 * Class DataRequest
 *
 * @package App\Http\Request\Api
 */
class FormRequest extends ApiRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
