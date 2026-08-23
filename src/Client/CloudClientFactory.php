<?php

namespace Kstmostofa\LaravelWhatsApp\Client;

use Kstmostofa\LaravelWhatsApp\Models\WaCloudAccount;

/**
 * `CloudClient` itself is a plain constructor-injected object — its Guzzle
 * client (and the access_token baked into its headers) is fixed at
 * construction with no setter. The service container binds ONE of it as a
 * singleton from `.env`/config for the legacy single-account setup. This
 * factory instead builds a FRESH `CloudClient` per call from a
 * `WaCloudAccount` row, so each account gets its own token/app/WABA — that's
 * the only way to support more than one Cloud API account at once.
 */
class CloudClientFactory
{
    public function forAccount(WaCloudAccount $account): CloudClient
    {
        return new CloudClient(
            baseHost: $account->base_host,
            apiVersion: $account->api_version,
            accessToken: $account->access_token,
            defaultPhoneNumberId: $account->phone_number_id,
            businessAccountId: $account->business_account_id,
        );
    }
}
