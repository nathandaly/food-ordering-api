<?php

namespace App\Entities\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class PaymentRequest
 * @package App\Entities\Payment
 */
class PaymentRequest extends Model
{
    protected $attributes = [
        'valueFeeTax',
        'localIdPaid',
        'valueFee',
        'module' => 'foodordering',
        'country',
        'valueNet',
        'valueNetTax',
        'valueGross',
        'currency',
        'description',
        'categoryId' => 0,
    ];

    protected $fillable = [
        'valueFeeTax',
        'localIdPaid',
        'valueFee',
        'module',
        'country',
        'valueNet',
        'valueNetTax',
        'valueGross',
        'currency',
        'description',
        'categoryId',
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
