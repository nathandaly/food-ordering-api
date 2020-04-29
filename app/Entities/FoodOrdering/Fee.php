<?php

namespace App\Entities\FoodOrdering;

/**
 * Class Fee
 * @package App\Entities\FoodOrdering
 */
class Fee
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $value;

    /**
     * @var string
     */
    public $target;

    /**
     * Fee constructor.
     * @param string $type
     * @param int $value
     * @param string $target
     */
    public function __construct(string $type = '', int $value = 0, string $target = '')
    {
        $this->type = $type;
        $this->value = $value;
        $this->target = $target;
    }

    /**
     * @param array $attributes
     * @return Fee
     */
    public function fill(array $attributes): self
    {
        return new self($attributes['type'], $attributes['value'], $attributes['target']);
    }
}
