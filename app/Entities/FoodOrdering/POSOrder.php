<?php

namespace App\Entities\FoodOrdering;

use App\Collections\POSOrderItems;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class POSOrder
 * @package App\Entities\FoodOrdering
 */
class POSOrder extends Model
{
    public const SOURCE_WEB = 0;
    public const SOURCE_ANDROID = 1;
    public const SOURCE_IOS = 2;

    public const DELIVERY_TIME_IN_ORDER = 0;
    public const DELIVERY_TIME_SPECIFIED_TIME = 1;

    /**
     * @var int
     */
    public $flgSource = self::SOURCE_IOS;

    /**
     * Uses prices provided by XML so this will always be 0.
     * @var int
     */
    public $flgUsePOSPrices = 0;

    /**
     * @var array
     */
    public $lgOrderType = 1;

    /**
     * @var string
     */
    public $flgDeliveryTime = self::DELIVERY_TIME_IN_ORDER;

    /**
     * Specifies payment type of online payment so this will always be 1.
     * @var int
     */
    public $flgPayment = 1;

    /**
     * @var string
     */
    public $phone = '';

    /**
     * @var string
     */
    public $clientIdentifier = '';

    /**
     * @var string
     */
    public $email = '';

    /**
     * @var string
     */
    public $loyaltyCard = '';

    /**
     * @var string
     */
    public $discountCoupon = '';

    /**
     * @var string
     */
    public $clientName = '';

    /**
     * @var string
     */
    public $comment = '';

    /**
     * @var int
     */
    public $deliveryPrice = 0;

    /**
     *  Our order reference (example: FO-5E6255F467534).
     *
     * @var string
     */
    public $uniqueId;

    /**
     * A collection of POSOrderItem.
     *
     * @var POSOrderItems
     */
    public $items = [];
}
