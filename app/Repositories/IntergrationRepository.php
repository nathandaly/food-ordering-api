<?php

namespace App\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class IntergrationRepository
 * @package App\Repositories
 */
class IntergrationRepository
{
    public function all(): Collection
    {
        $query = DB::query()
            ->select('*')
            ->from('intergration_config', 'ic')
            ->join('intergration_system_fields', 'isf');

       // $query->
    }
}
