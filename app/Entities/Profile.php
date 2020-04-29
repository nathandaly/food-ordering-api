<?php

namespace App\Entities;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * App\Entities\Profile
 *
 * @property int $id
 * @property string|null $uuid
 * @property string $email
 * @property string $firstname
 * @property string $lastname
 * @property string $phone
 * @property string $password
 * @property string $password2
 * @property \Illuminate\Support\Carbon $createddate
 * @property \Illuminate\Support\Carbon $modifieddate
 * @property int $marketing0
 * @property int $password_failed_attempts
 * @property int $password_timeout
 * @property string $password_failed_date
 * @property int $avatar_set
 * @property string|null $external_id
 * @property string|null $tags
 * @property string|null $security_email
 * @property string|null $security_phone
 * @property string|null $deleteddate_requested
 * @property string|null $gdpr_auto_notified
 * @property string|null $gdpr_auto_notified_final
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereAvatarSet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereCreateddate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereDeleteddateRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereGdprAutoNotified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereGdprAutoNotifiedFinal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereMarketing0($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereModifieddate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile wherePassword2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile wherePasswordFailedAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile wherePasswordFailedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile wherePasswordTimeout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereSecurityEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereSecurityPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Profile whereUuid($value)
 * @mixin \Eloquent
 */
class Profile extends Authenticatable
{
    use Notifiable;

    const CREATED_AT = 'createddate';
    const UPDATED_AT = 'modifieddate';

    /**
     * @var array
     */
    protected $fillable = [
        'email',
        'firstname',
        'lastname',
        'phone',
        'uuid',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
        'password0',
    ];

    public function profileLocals()
    {
        return $this
            ->hasMany(ProfileLocal::class, 'profileid', 'id');
    }

    public function locals()
    {
        return $this
            ->belongsToMany(
                Local::class,
                ProfileLocal::class,
                'profileid',
                'localid'
            )
            ->withPivot([
                'primary_local',
                'last_login',
                'centreid',
                'roleid',
            ]);
    }
}
