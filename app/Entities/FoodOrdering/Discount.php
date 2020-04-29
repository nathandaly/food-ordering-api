<?php

namespace App\Entities\FoodOrdering;

use App\Entities\BaseModel;
use App\Entities\Centre;
use App\Entities\Local;
use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class Discount
 * @package App\Entities\FoodOrdering
 * @property int $id
 * @property int $centre_id
 * @property int $local_id
 * @property string $code
 * @property int $quantity
 * @property string $description
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime $expires_at
 * @property DateTime $deleted_at
 *
 * @property Collection items
 *
 * @method static where(array $array)
 */
class Discount extends BaseModel
{
    use SoftDeletes;

    /**
     * @var bool
     */
    public $timestamps = true;

    /**
     * The table associated with Discount
     * @var string
     */
    protected $table = 'fo_discounts';

    /**
     * @var array
     */
    protected $fillable = [
        'centre_id',
        'local_id',
        'code',
        'quantity',
        'description',
        'items',
    ];

    protected $hidden = [
        'id',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function getQuantityAttribute($value)
    {
        if ($value === 0) {
            return -1;
        }

        return $value;
    }

    /**
     * @return BelongsTo
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class);
    }

    /**
     * @return BelongsTo
     */
    public function local(): BelongsTo
    {
        return $this->belongsTo(Local::class);
    }

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(DiscountItems::class);
    }

    /**
     * @return HasMany
     */
    public function used(): HasMany
    {
        return $this->hasMany(DiscountsApplied::class);
    }
}
