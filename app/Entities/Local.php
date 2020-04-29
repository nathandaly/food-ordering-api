<?php

namespace App\Entities;

/**
 * Class Local
 *
 * @package App
 * @property $id
 */
class Local extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'local_orgs';

    public function profileLocals()
    {
        return $this->hasMany('App\ProfileLocal', 'localid', 'id');
    }
}
