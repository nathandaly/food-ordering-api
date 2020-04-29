<?php

namespace App\Traits;

use Illuminate\Validation\Validator;

/**
 * Trait ValidateLocalParam
 * @package App\Traits
 */
trait ValidateLocalParam
{
    /**
     * Configure the validator instance.
     *
     * @param  Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $localParam = $this->route('local');

        $validator->after(static function (Validator $validator) use ($localParam) {
            if ($localParam === null) {
                $validator->errors()->add('local', 'Local ID is missing from route params.');
            }
        });
    }
}
