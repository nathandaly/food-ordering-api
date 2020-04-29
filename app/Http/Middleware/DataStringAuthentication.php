<?php


namespace App\Http\Middleware;

use App\Entities\Profile;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Class DataStringAuthentication
 * @package App\Http\Middleware\data
 */
class DataStringAuthentication extends BaseApiMiddleware
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
        $profile = null;

        try {
            $profileId = $request->data['profileid'];

            if (!Auth::check()) {
                if (!$profile = Profile::find($profileId)) {
                    throw new \RuntimeException('No profile object found for profile ID ' . $profileId, 404);
                }

                if ($profile->uuid === null) {
                    $profile->update(['uuid' => Str::uuid()]);
                }

                $profileLocal = $profile
                    ->profileLocals()
                    ->where('profile_locals.localid', $request->data['localid'])
                    ->first();

                if ($profileLocal && $profileLocal->api_token === null) {
                    $profileLocal->update(['api_token', Str::random(60)]);
                }

                Auth::login($profile);
            }

            $request->request->add([
                'profile' => $profile,
            ]);
        } catch (QueryException $e) {
            return $this->respondError(
                'Profile verification error: ' . $e->getMessage(),
                1,
                500
            );
        }
        catch (\RuntimeException $e) {
            return $this->respondError(
                'Profile verification error: ' . $e->getMessage()
            );
        }

        return $next($request);
    }
}
