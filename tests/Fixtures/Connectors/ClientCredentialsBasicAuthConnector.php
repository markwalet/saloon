<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\ClientCredentialsBasicAuthGrant;

class ClientCredentialsBasicAuthConnector extends Connector
{
    use ClientCredentialsBasicAuthGrant;

    /**
     * Define the base URL.
     */
    public function resolveBaseUrl(): string
    {
        return 'https://oauth.saloon.dev';
    }

    /**
     * Define default Oauth config.
     */
    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId('client-id')
            ->setClientSecret('client-secret');
    }
}
