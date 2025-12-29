<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $site_name
 * @property string $contact_email
 * @property string|null $contact_phone
 * @property string|null $contact_address
 * @property string|null $telegram_link
 * @property string|null $instagram_link
 * @property string|null $somontj_link
 * @property string|null $address
 * @property string|null $address_on_map
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereAddressOnMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereContactAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereInstagramLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereSiteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereSomontjLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereTelegramLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSettings whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GeneralSettings extends Model
{
    protected $fillable = [
        'site_name',
        'contact_email',
        'contact_phone',
        'contact_address',
        'telegram_link',
        'instagram_link',
        'somontj_link',
        'address',
        'address_on_map'
    ];
}
