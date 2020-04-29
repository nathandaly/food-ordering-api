<?php

namespace App\Repositories;

use App\Entities\Address;
use App\Entities\Intergration\System;
use App\Entities\Intergration\SystemField;
use App\Entities\LatLong;
use App\Entities\Local;

/**
 * Class RestaurantRepository
 * @package App\Repositories
 */
class RestaurantRepository extends Repository
{
    /**
     * @param int $id
     * @return Address|null
     */
    public function fetchAddress(int $id): ?Address
    {
        if (!$local = Local::find($id)->first()) {
            return null;
        }

        return (new Address())->fill([
            'unitNumber' => $local->address1,
            'line1' => $local->address2,
            'line2' => $local->address3,
            'town' => $local->address4,
            'postcode' => $local->postcode,
            'phone' => $local->phone,
            'location' => new LatLong(0, 0),
        ]);
    }

    /**
     * @param string $id
     * @return string|null
     */
    public function queryIntegrationInternalUrl(string $id): ?string
    {
        $integration = System::from('intergration_systems as is')
            ->join('intergration_system_fields as isf', \DB::raw('isf.system_id'), '=', \DB::raw('is.id'))
            ->join('intergration_data as id', \DB::raw('id.field_id'), '=', \DB::raw('isf.id'))
            ->where('isf.type_id', SystemField::integrationTypes['API_URL'])
            ->where('id.internal_id', (int) $id)
            ->where('isf.deleted', 0)
            ->first();

        if (!$integration || !$integration->field_value) {
            return null;
        }

        return $integration->field_value;
    }

    /**
     * @return string
     */
    public function modelClass(): string
    {
        return Local::class;
    }
}
