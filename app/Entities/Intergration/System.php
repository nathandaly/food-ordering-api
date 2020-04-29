<?php

namespace App\Entities\Intergration;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * @var string
     */
    protected $table = 'intergration_systems';
}
