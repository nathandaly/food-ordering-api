<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 *
 * @package App
 * @property string $unitNumber
 * @property string $line1
 * @property string $line2
 * @property string $town
 * @property string $area
 * @property string $postcode
 * @property string $phone
 * @property LatLong $location
 */
class Address extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'unitNumber',
        'line1',
        'line2',
        'town',
        'area',
        'postcode',
        'phone',
        'location',
    ];
}
