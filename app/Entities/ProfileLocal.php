<?php

namespace App\Entities;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ProfileLocal
 *
 * @package App
 */
class ProfileLocal extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'profile_locals';

    protected $fillable = [
        'api_token',
    ];

    public function profile()
    {
        return $this->belongsTo('App\Entities\Profile', 'profileid', 'id');
    }
}
