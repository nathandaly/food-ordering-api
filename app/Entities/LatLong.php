<?php

namespace App\Entities;

/**
 * Class LatLong
 * @package App\Entities
 */
class LatLong
{
    /**
     * @var float
     */
    public $latitude = 0;

    /**
     * @var float
     */
    public $longitude = 0;

    /**
     * LatLong constructor.
     * @param int $latitude
     * @param int $longitude
     */
    public function __construct(int $latitude = 0, int $longitude = 0)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->latitude . ', ' . $this->longitude;
    }
}
