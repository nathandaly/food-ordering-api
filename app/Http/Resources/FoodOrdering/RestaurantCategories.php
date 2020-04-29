<?php

namespace App\Http\Resources\FoodOrdering;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class RestaurantCategories
 * @package App\Http\Resources\FoodOrdering
 */
class RestaurantCategories extends ResourceCollection
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        self::withoutWrapping();
        $formatted = [];

        $this->collection->map(static function ($category) use (&$formatted) {
            $formatted[] = [
                'id' => (int) $category['POSPRODGRPID'],
                'name' => $category['Name'],
                'description' => $category['Description'] ?? null,
                'image' => $category['Image'] ?? null, // TODO: Need to get this implemented.
            ];
        });

        return $formatted;
    }
}
