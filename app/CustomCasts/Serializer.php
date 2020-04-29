<?php

namespace App\CustomCasts;

use Vkovic\LaravelCustomCasts\CustomCastBase;

/**
 * Class Serializer
 * @package App\CustomCasts
 */
class Serializer extends CustomCastBase
{
    /**
     * @param mixed $value
     * @return mixed|string
     */
    public function setAttribute($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return serialize($value);
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function castAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return json_decode(unserialize($value, ['allowed_classes' => false]), true);
    }
}
