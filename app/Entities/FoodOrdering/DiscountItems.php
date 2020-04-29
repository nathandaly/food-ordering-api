<?php

namespace App\Entities\FoodOrdering;

use App\Entities\BaseModel;
use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DiscountItems
 * @package App\Entities\FoodOrdering
 * @property int $id
 * @property int $discount_id
 * @property string $target,
 * @property string $type,
 * @property int $value,
 * @property DateTime $created_at,
 * @property DateTime $updated_at,
 * @property DateTime $expires_at,
 * @property DateTime $deleted_at,
 *
 */
class DiscountItems extends BaseModel
{
    use SoftDeletes;

    /**
     * Where will the discount apply to?
     *  - Order total
     *  - Delivery cost
     */
    public const TARGET_TOTAL = 'total';
    public const TARGET_DELIVERY = 'delivery';

    /**
     * Type of discount being applied
     */
    public const TYPE_ABSOLUTE = 'absolute';
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FREE = 'free';

    /**
     * @var string[]
     */
    public const TARGETS = [
        'total' => self::TARGET_TOTAL,
        'delivery' => self::TARGET_DELIVERY,
    ];

    /**
     * @var string[]
     */
    public const TYPES = [
        'absolute' => self::TYPE_ABSOLUTE,
        'percent' => self::TYPE_PERCENT,
        'free' => self::TYPE_FREE,
    ];

    /**
     * The table associated with Discount
     * @var string
     */
    protected $table = 'fo_discount_items';

    /**
     * @var array
     */
    protected $fillable = [
        'discount_id',
        'target',
        'type',
        'value',
        'created_at',
        'updated_at',
        'expires_at',
        'deleted_at',
    ];

    protected $hidden = [
        'id',
        'discount_id',
    ];

    /**
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * @return array|string[]
     */
    public function getTargets(): array
    {
        return self::TARGETS;
    }

    /**
     * @return array|string[]
     */
    public function getTypes(): array
    {
        return self::TYPES;
    }
}
