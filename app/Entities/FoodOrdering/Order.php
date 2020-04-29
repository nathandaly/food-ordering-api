<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Entities\FoodOrdering\Order
 *
 * @property int basket_id
 * @property string status
 * @property string updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\FoodOrdering\Order query()
 * @method public array getTotals()
 * @mixin \Eloquent
 */
class Order extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_EDITING = 'EDITING';
    public const STATUS_PAYMENT_PENDING = 'PAYMENT_PENDING';
    public const STATUS_PAYMENT_COMPLETE = 'PAYMENT_COMPLETE';
    public const STATUS_SENT = 'SENT';
    // START FOOD PROVIDER
    public const STATUS_PREPARING = 'STATUS_PREPARING';
    public const STATUS_COLLECTION_READY = 'STATUS_COLLECTION_READY';
    public const STATUS_DISPATCHED = 'STATUS_DISPATCHED';
    // END FOOD PROVIDER
    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_ABANDONED = 'ABANDONED';
    public const STATUS_USER_DELETED = 'USER_DELETED';

    public const STATUSES = [
        'pending' => self::STATUS_PENDING,
        'editing' => self::STATUS_EDITING,
        'payment_pending' => self::STATUS_PAYMENT_PENDING,
        'received ' => self::STATUS_PAYMENT_COMPLETE,
        'sent' => self::STATUS_SENT,
        'preparing' => self::STATUS_PREPARING,
        'collection_ready ' => self::STATUS_COLLECTION_READY,
        'dispatched' => self::STATUS_DISPATCHED,
        'complete' => self::STATUS_COMPLETE,
        'abandoned' => self::STATUS_ABANDONED,
        'user_deleted' => self::STATUS_USER_DELETED,
    ];

    /**
     * the table associated with food orders
     * @var string
     */
    protected $table = 'fo_orders';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $hidden = [
        'id'
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'basket_id',
        'order_ref',
        'status',
        'total_payment_price',
        'net_cost',
        'tax_cost',
        'gross_cost',
        'net_admin_fee',
        'tax_admin_fee',
        'gross_admin_fee',
        'iso_currency_code',
        'meta_data',
        'disputed',
    ];

    /**
     * Get the route key for the model (used during route binding).
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'order_ref';
    }

    public function basket()
    {
        return $this->hasOne(Basket::class);
    }

    /**
     * @return array
     */
    public function getTotals(): array
    {
        return [
            'total_payment_price' => $this->total_payment_price,
            'net_cost' => $this->net_cost,
            'tax_cost' => $this->tax_cost,
            'gross_cost' => $this->gross_cost,
            'net_admin_fee' => $this->net_admin_fee,
            'tax_admin_fee' => $this->tax_admin_fee,
            'gross_admin_fee' => $this->gross_admin_fee,
            'iso_currency_code' => $this->iso_currency_code,
        ];
    }

    /**
     * @param array $attributes
     * @return Order
     */
    public function setMetadata(array $attributes): self
    {
        $metaData = [];

        if (is_array($this->meta_data)) {
            $metaData = $this->meta_data;
        }

        $this->meta_data = array_merge($metaData, $attributes);

        return $this;
    }
}
