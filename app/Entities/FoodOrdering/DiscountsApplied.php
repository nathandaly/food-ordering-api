<?php

namespace App\Entities\FoodOrdering;

use App\Entities\BaseModel;
use App\Entities\Profile;
use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class DiscountsApplied
 * @package App\Entities\FoodOrdering
 * @property int $id
 * @property int $discount_id
 * @property int $order_id
 * @property int profile_id
 * @property string $description
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime $expires_at
 * @property DateTime $deleted_at
 *
 */
class DiscountsApplied extends BaseModel
{
    use SoftDeletes;

    /**
     * The table associated with DiscountsApplied
     * @var string
     */
    protected $table = 'fo_discounts_applied';

    /**
     * @var array
     */
    protected $fillable = [
        'discount_id',
        'order_id',
        'profile_id',
        'description',
        'created_at',
        'updated_at',
        'expires_at',
        'deleted_at',
    ];

    protected $hidden = [
        'id',
        'discount_id',
        'order_id',
        'profile_id',
    ];

    /**
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
