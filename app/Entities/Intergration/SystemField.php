<?php

namespace App\Entities\Intergration;

use App\FoodOrdering\IntegrationType;
use Illuminate\Database\Eloquent\Model;

class SystemField extends Model
{
    public const integrationTypes = [
      'APP' => IntegrationType::ID_TYPE_APP,
      'OWNER' => IntegrationType::ID_TYPE_OWNER,
      'CENTRE' => IntegrationType::ID_TYPE_CENTRE,
      'LOCAL' => IntegrationType::ID_TYPE_LOCAL,
      'PROFILE' => IntegrationType::ID_TYPE_PROFILE,
      'ROLE' => IntegrationType::ID_TYPE_ROLE,
      'API_URL' => IntegrationType::ID_TYPE_API_URL,
    ];

    protected $table = 'intergration_system_fields';
}
