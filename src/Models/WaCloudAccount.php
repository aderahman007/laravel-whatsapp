<?php

namespace Kstmostofa\LaravelWhatsApp\Models;

use Illuminate\Database\Eloquent\Model;
use Kstmostofa\LaravelWhatsApp\Models\Concerns\UsesWhatsAppConnection;

/**
 * A Cloud API "account" — one Meta app/WABA/access_token/app_secret
 * combination, owned by exactly one Laravel user (same strict isolation
 * model as WaSession). Lets Compose send through multiple, fully separate
 * Meta Business accounts instead of the single .env-configured one.
 *
 * @property string $id  user-chosen slug, e.g. "acc-main"
 * @property ?int $user_id
 * @property string $label
 * @property string $phone_number_id
 * @property ?string $business_account_id
 * @property string $access_token  encrypted at rest
 * @property string $app_secret  encrypted at rest
 * @property ?string $verify_token
 * @property string $api_version
 * @property string $base_host
 * @property string $status
 */
class WaCloudAccount extends Model
{
    use UsesWhatsAppConnection;

    protected $table = 'wa_cloud_accounts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = [
        'access_token' => 'encrypted',
        'app_secret' => 'encrypted',
    ];

    protected $hidden = [
        'access_token',
        'app_secret',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
