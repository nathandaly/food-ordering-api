<?php

namespace App\FoodOrdering;

/**
 * Class IntegrationType
 * @package App
 */
abstract class IntegrationType
{
    /**
     * @var int
     */
    public const ID_TYPE_APP = 1;

    /**
     * @var int
     */
    public const ID_TYPE_OWNER = 2;

    /**
     * @var int
     */
    public const ID_TYPE_CENTRE = 3;

    /**
     * @var int
     */
    public const ID_TYPE_LOCAL = 4;

    /**
     * @var int
     */
    public const ID_TYPE_PROFILE = 5;

    /**
     * @var int
     */
    public const ID_TYPE_ROLE = 6;

    /**
     * @var int
     */
    public const ID_TYPE_API_URL = 9;
}
