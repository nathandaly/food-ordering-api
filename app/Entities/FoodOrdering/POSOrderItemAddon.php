<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class POSOrderItemAddon
 * @package App\Entities\FoodOrdering
 */
class POSOrderItemAddon extends Model
{
    /**
     * Product ID.
     *
     * @var int
     */
    public $POSPRODID = 0;

    /**
     * Group type ID.
     *
     * @var int
     */
    public $POSDODGRPID = 0;

    /**
     * Quantity of addon per single unit of item above.
     *
     * @var int
     */
    public $quantity = 0;

    /**
     * Price of single unit of AddOn.
     *
     * @var int
     */
    public $price = 0;
}
