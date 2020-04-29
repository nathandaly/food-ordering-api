<?php

namespace App\Http\Resources\FoodOrdering;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RestaurantShow
 * @package App\Http\Resources\FoodOrdering
 */
class RestaurantShow extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
