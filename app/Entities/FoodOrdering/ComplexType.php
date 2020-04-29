<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Entities\FoodOrdering\ComplexType
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\ComplexType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\ComplexType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\ComplexType query()
 * @mixin \Eloquent
 */
class ComplexType extends Model
{
    /**
     * the table associated with food ordering complex products types
     * @var string
     */
    protected $table = 'complex_types';

    /**
     * @var string
     */
    protected $primaryKey = 'id';
}
