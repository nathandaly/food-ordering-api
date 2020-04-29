<?php

namespace App\Http\Middleware\Data\Normalisation;

use App\Entities\Centre as CentreModel;
use App\Http\Middleware\BaseApiMiddleware;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

/**
 * Class Centre
 * @package App\Http\Middleware\data\normalisation
 */
class Centre extends BaseApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $centreId = $request->data['centreid'];
            $centre = CentreModel::find($centreId);

            if ($centre) {
                $centre = CentreModel::find($centreId);

                if ($config = $centre->config->where('config_key', 'food_ordering_module_config')->first()) {
                    $centre->config = @unserialize($config['config_value'], ['allowed_classes' => false]);
                }

                $request->request->add([
                    'centre' => $centre,
                ]);
            }
        } catch (\RuntimeException $e) {
            return $this->respondError(
                'Centre normalisation error: ' . $e->getMessage()
            );
        }

        return $next($request);
    }
}
