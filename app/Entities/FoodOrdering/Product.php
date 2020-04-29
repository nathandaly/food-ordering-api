<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Entities\FoodOrdering\Product
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Product query()
 * @mixin \Eloquent
 */
class Product extends Model
{
    /**
     * the table associated with food ordering products
     * @var string
     */
    protected $table = 'fo_products';

    /**
     * @var string
     */
    protected $primaryKey = 'id';
}
