<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Centre
 *
 * @package App
 */
class Centre extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'centres';

    /**
     * @return HasOne
     */
    public function config(): HasOne
    {
        return $this->hasOne(CentreConfig::class, 'centreid', 'id');
    }
}
