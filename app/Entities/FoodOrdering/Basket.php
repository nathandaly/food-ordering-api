<?php

namespace App\Entities\FoodOrdering;

use App\CustomCasts\Serializer;
use App\Entities\BaseModel;
use App\Entities\Centre;
use App\Entities\Local;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Vkovic\LaravelCustomCasts\HasCustomCasts;

/**
 * Class Basket
 * @package App\Entities\FoodOrdering
 * @property string $uuid
 * @property string $basket
 * @property int profile_id,
 * @property int centre_id,
 * @property int local_id,
 * @method static create(array $array)
 */
class Basket extends BaseModel
{
    use SoftDeletes, HasCustomCasts;

    public const STATUS_CREATED = 'CREATED';
    public const STATUS_ORDERED = 'ORDERED';

    public const STATUSES = [
      'created' => self::STATUS_CREATED,
      'ordered' => self::STATUS_ORDERED,
    ];

    /**
     * The table associated with baskets
     * @var string
     */
    protected $table = 'fo_basket';

    protected $attributes = [
        'updated_at' => null,
        'deleted_at' => null,
        'basket' => [],
    ];

    protected $casts = [
        'basket' => Serializer::class
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'uuid',
        'profile_id',
        'centre_id',
        'local_id',
        'basket',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted',
    ];

    protected $hidden = [
      'id',
      'profile_id',
      'local_id',
      'centre_id',
    ];

    /**
     * Basket constructor.
     * @param array $attributes
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        if (empty($attributes['uuid'])) {
            $attributes['uuid'] = Str::uuid();
        }

        if (empty($attributes['created_date'])) {
            $attributes['created_date'] = (new \DateTime())->format(DATE_ATOM);
        }

        if (empty($attributes['status'])) {
            $attributes['status'] = self::STATUSES['created'];
        }

        parent::__construct($attributes);
    }

    /**
     * Get the route key for the model (used during route binding).
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return HasOne
     */
    public function local(): HasOne
    {
        return $this->hasOne(Local::class, 'id', 'local_id');
    }

    /**
     * @return BelongsTo
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class);
    }
}
