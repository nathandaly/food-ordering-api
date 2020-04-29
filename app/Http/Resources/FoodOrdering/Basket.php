<?php

namespace App\Http\Resources\FoodOrdering;

use App\Http\Resources\ResourcesNoWrapping;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request as RequestAlias;

/**
 * Class Basket
 * @package App\Http\Resources\FoodOrdering
 */
class Basket extends ResourcesNoWrapping
{
    use SoftDeletes;

    /**
     * Transform the resource into an array.
     *
     * @param RequestAlias $request
     * @return array
     */
    public function toArray($request): array
    {
        $basket = parent::toArray($request);

        $basket = ['created_at' => $basket['created_at']] + $basket;
        $basket = ['basket' => $basket['basket']] + $basket;
        $basket = ['uuid' => $basket['uuid']] + $basket;
        unset($basket['profile_id'], $basket['centre_id']);

        return $basket;
    }
}
