<?php

namespace App\Entities\FoodOrdering;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Delivery
 * @package App\Entities\FoodOrdering
 */
class Delivery extends Model
{
    protected $attributes = [
        'type',
        'fee',
    ];

    protected $fillable = [
        'type',
        'fee',
    ];

    /**
     * @return array
     */
    public function toArray(): array
    {
        $paymentArray = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (is_int($key)) {
                continue;
            }

            $paymentArray[Str::snake($key)] = $value;
        }

        return $paymentArray;
    }
}
