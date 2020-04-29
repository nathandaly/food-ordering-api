<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Push;

/**
 * Class DeviceTokenRepository
 * @package App\Repositories
 */
class DeviceTokenRepository
{
    /**
     * @param array $profileIds
     * @return array
     */
    public function getProfilesTokens(array $profileIds): array
    {
        if (empty($profileIds)) {
            return [];
        }

        return DB::table('apn')
            ->select(['devices.token', 'devices.device_os', 'devices.device_make'])
            ->leftJoin('profile_status', function($join) {
                $join->on('apn.profileid', '=', 'profile_status.profileid');
                $join->on('apn.centreid', '=', 'profile_status.centreid');
            })
            ->leftJoin('devices', function($join) {
                $join->on('apn.deviceid', '=', 'devices.id');
            })
            ->whereIn('apn.profileid', $profileIds)
            ->where('profile_status.email_verified', '=', 1)
            ->where('profile_status.approved', '=', 1)
            ->where('apn.modifieddate', '>', 'NOW() - INTERVAL 90 DAY')
            ->groupBy('devices.token')
            ->get()
            ->toArray();
    }

    /**
     * @param array $localIds
     * @return array
     */
    public function getLocalTokens(array $localIds): array
    {
        if (empty($localIds)) {
            return [];
        }

        return DB::table('apn')
            ->select(['d.token', 'd.device_os', 'd.device_make'])
            ->leftJoin('devices AS d', 'apn.deviceid','=', 'd.id')
            ->leftJoin('profile_status AS ps', function($join) {
                $join->on('apn.profileid', '=', 'ps.profileid');
                $join->on('apn.centreid', '=', 'ps.centreid');
            })
            ->whereIn('apn.localid', $localIds)
            ->where('ps.email_verified', '=', 1)
            ->where('ps.approved', '=', 1)
            ->where('apn.modifieddate', '>', 'NOW() - INTERVAL 90 DAY') // Tokens expire after a month
            ->groupBy('d.token')
            ->get()
            ->toArray();
    }

    /**
     * @param int $centreId
     * @param array $groupIds
     * @return array
     */
    public function getGroupTokens(int $centreId, array $groupIds): array
    {
        if (empty($groupIds)) {
            return [];
        }

        return DB::table('apn as a')
            ->select(['d.token', 'd.device_os', 'd.device_make'])
            ->leftJoin('devices AS d', 'a.deviceid','=', 'd.id')
            ->leftJoin('local_groups AS lg', function($join) {
                $join->on('a.localid', '=', 'lg.localid');
                $join->on('a.roleid', '=', 'lg.roleid');
            })
            ->leftJoin('profile_status AS ps', function($join) {
                $join->on('a.profileid', '=', 'ps.profileid');
                $join->on('a.centreid', '=', 'ps.centreid');
            })
            ->where('a.centreid', '=', $centreId)
            ->where('a.modifieddate', '>', 'NOW() - INTERVAL 90 DAY') // Tokens expire after a month
            ->where('ps.email_verified', '=', 1)
            ->where('ps.approved', '=', 1)
            ->whereIn('lg.groupid', $groupIds)
            ->groupBy('d.token')
            ->get()
            ->toArray();
    }

    /**
     * @param int $centreId
     * @param $tokens
     * @return array
     */
    public function sortTokens(int $centreId, $tokens): array
    {
        $tokens_array = [];

        if(count($tokens) > 0) {

            foreach($tokens as $i => $t){

                if(preg_match('(SIMULATOR|null)', $t->token) === 1) {
                    unset($tokens);
                    continue;
                }

                // Set OS
                $os = strtolower($t->device_os);
                if(empty($t->device_os)) {
                    switch ($t->device_make) {
                        case 'android':
                            $os = 'android';
                            break;
                        case 'iPad':
                        case 'iPhone':
                            $os = 'ios';
                            break;
                    }
                }

                if (empty($tokens_array[$os])) $tokens_array[$os] = [];
                $tokens_array[$os][$t->token] = [
                    'groupid'	=>	$centreId,
                    'centreid'	=>	$centreId,
                    'rowid' => 	0,
                    'count'	=>	1,
                    'device_make' => strtolower($t->device_make)
                ];
            }
        }

        return $tokens_array;
    }

    /**
     * @param int $appId
     * @param int $centreId
     * @param array $tokensArray
     * @param array $payload
     * @return array|bool[]
     */
    public function sendPush(int $appId, int $centreId, array $tokensArray = [], array $payload): array
    {
        $push = new Push();
        $push->setup($appId);
        $push->logger = false;
        $errors = [
            'ios' => false,
            'android' => false,
            'windows' => false,
        ];

        $payLoad = array_merge([
            'scheduleid' => 0,
            'streamid' => 0,
            'categoryid' => 0,
            'title' => $payload['title'],
            'category' => $payload['title'],
            'centreid' => $centreId,
        ], $payload);

        if (!empty($tokensArray['ios'])) {
            try {
                $push->sendApple([
                    'tokens' => $tokensArray['ios'],
                    'payload' => $payLoad
                ]);
            } catch (Exception $e) {
                $errors['ios'] = true;
                Log::error($e->getMessage());
            }
        }

        if (!empty($tokensArray['android'])) {
            try {
                $push->sendAndroid([
                    'tokens' => $tokensArray['android'],
                    'payload' => $payLoad
                ]);
            } catch (Exception $e) {
                $errors['android'] = true;
                Log::error($e->getMessage());
            }
        }

        if (!empty($tokensArray['windows'])) {
            try {
                $push->sendWindows([
                    'tokens' => $tokensArray['windows'],
                    'payload' => $payLoad
                ]);
            } catch (Exception $e) {
                $errors['windows'] = true;
                Log::error($e->getMessage());
            }
        }

        return $errors;
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function modelClass(): string
    {
        // TODO: Implement modelClass() method.
    }
}
