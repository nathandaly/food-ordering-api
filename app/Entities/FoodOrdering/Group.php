<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Entities\FoodOrdering\Group
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Group query()
 * @mixin \Eloquent
 */
class Group extends Model
{
    /**
     * the table associated with food ordering groups
     * @var string
     */
    protected $table = 'fo_groups';

    /**
     * @var string
     */
    protected $primaryKey = 'id';
}
