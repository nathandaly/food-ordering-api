<?php

namespace App\Entities\FoodOrdering;

use App\Collections\POSOrderItemAddons;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class POSOrderItem
 * @package App\Entities\FoodOrdering
 */
class POSOrderItem extends Model
{
    public const ITEM_NOT_COMPLEX = 0;
    public const ITEM_COMPLEX = 1;

    /**
     * Required, to make sure we store items in the same order.
     * @var int
     */
    protected $flgRowNo = 0;

    /**
     * @var int
     */
    protected $flgDivided = self::ITEM_NOT_COMPLEX;

    /**
     * If flgDivided then what part number?
     *
     * @var int
     */
    protected $flgPart = 0;

    /**
     * @var int
     */
    protected $POSPRODID = 0;

    /**
     * @var int
     */
    protected $groupID = 0;

    /**
     * Dimension 1 for complex type (0 for ordinary type).
     *
     * @var int
     */
    protected $POSPIZZACIAID = 0;

    /**
     * Dimension 2 for complex type (0 for ordinary type).
     *
     * @var int
     */
    protected $POSPIZZAROZID = 0;

    /**
     * @var int
     */
    protected $quantity = 0;

    /**
     * Price of single unit of item (excluding addons).
     *
     * @var int
     */
    protected $price = 0;

    /**
     * @var string
     */
    protected $comment = '';

    /**
     * A collection of POSOrderItemAddon.
     *
     * @var POSOrderItemAddons
     */
    protected $addons = [];
}
