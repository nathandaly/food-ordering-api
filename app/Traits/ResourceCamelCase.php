<?php

namespace App\Traits;

use Illuminate\Http\Request as RequestAlias;
use Illuminate\Support\Str;

trait ResourceCamelCase
{
    /**
     * Transform the resource into an array.
     *
     * @param RequestAlias $request
     * @return array
     */
    public function toArray($request): array
    {
        $object = parent::toArray($request);

        foreach ($object as $fieldName => $value) {
            if ($fieldName !== Str::camel($fieldName)) {
                $object[Str::camel($fieldName)] = $value;
                unset($object[$fieldName]);
            }
        }

        return $object;
    }
}
