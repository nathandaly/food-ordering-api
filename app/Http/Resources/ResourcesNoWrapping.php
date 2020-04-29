<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RequestNoWrapping
 * @package App\Http\Resources
 */
class ResourcesNoWrapping extends JsonResource
{
    /**
     * Basket constructor.
     * @param $resource
     */
    public function __construct($resource)
    {
        parent::__construct($resource);
        self::withoutWrapping();
    }
}
