<?php

declare(strict_types=1);

namespace App\Services;

class SocialAuthService
{
    /**
     * Get list of available social providers with configured credentials.
     */
    public static function getAvailableProviders(): array
    {
        $providers = [];

        if (self::isProviderConfigured('google')) {
            $providers[] = 'google';
        }

        if (self::isProviderConfigured('github')) {
            $providers[] = 'github';
        }

        if (self::isProviderConfigured('facebook')) {
            $providers[] = 'facebook';
        }

        if (self::isProviderConfigured('twitter')) {
            $providers[] = 'twitter';
        }

        if (self::isProviderConfigured('linkedin')) {
            $providers[] = 'linkedin';
        }

        return $providers;
    }

    /**
     * Check if a social provider is properly configured.
     */
    private static function isProviderConfigured(string $provider): bool
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        return ! empty($clientId) && ! empty($clientSecret);
    }
}
