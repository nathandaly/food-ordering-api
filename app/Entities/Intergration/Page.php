<?php

namespace App\Entities\Intergration;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Page
 * @package App\Entities\Intergration
 */
class Page extends Model
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
    protected $table = 'intergration_pages';
}
