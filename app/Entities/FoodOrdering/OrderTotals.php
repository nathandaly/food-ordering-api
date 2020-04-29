<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class OrderTotals
 * @package App\Entities\FoodOrdering
 */
class OrderTotals extends Model
{
    protected $fillable = [
        'total',
        'extVATTotal',
        'currencyCode',
        'fees',
    ];

    /**
     * @var int
     */
    public $total;

    /**
     * @var int
     */
    public $extVATTotal;

    /**
     * @var string
     */
    public $currencyCode = 'PLN';

    /**
     * @var Collection
     */
    public $fees = [];

    /**
     * @param array $fees
     * @return $this
     */
    public function setFeesAttribute(array $fees): self
    {
        $this->attributes['fees'] = Collection::make($fees);

        return $this;
    }
}
