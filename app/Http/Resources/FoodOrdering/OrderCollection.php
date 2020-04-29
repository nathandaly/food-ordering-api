<?php

namespace App\Http\Resources\FoodOrdering;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class OrderCollection
 * @package App\Http\Resources\FoodOrdering
 */
class OrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        self::withoutWrapping();

        return parent::toArray($request);
    }
}
