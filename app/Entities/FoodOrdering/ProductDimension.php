<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Entities\FoodOrdering\ProductDimension
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\ProductDimension newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\ProductDimension newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\ProductDimension query()
 * @mixin \Eloquent
 */
class ProductDimension extends Model
{
    /**
     * the table associated with food ordering product dimensions
     * @var string
     */
    protected $table = 'fo_product_dimensions';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

}
