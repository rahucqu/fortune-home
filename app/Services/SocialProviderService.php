<?php

declare(strict_types=1);

namespace App\Services;

class SocialProviderService
{
    /**
     * Get available social providers with credentials configured.
     */
    public function getAvailableProviders(): array
    {
        $providers = [
            'google' => [
                'name' => 'google',
                'label' => 'Continue with Google',
                'bgColor' => 'bg-white hover:bg-gray-50',
                'textColor' => 'text-gray-900',
                'icon' => 'google',
            ],
            'github' => [
                'name' => 'github',
                'label' => 'Continue with GitHub',
                'bgColor' => 'bg-gray-900 hover:bg-gray-800',
                'textColor' => 'text-white',
                'icon' => 'github',
            ],
            'facebook' => [
                'name' => 'facebook',
                'label' => 'Continue with Facebook',
                'bgColor' => 'bg-blue-600 hover:bg-blue-700',
                'textColor' => 'text-white',
                'icon' => 'facebook',
            ],
            'twitter' => [
                'name' => 'twitter',
                'label' => 'Continue with Twitter',
                'bgColor' => 'bg-sky-500 hover:bg-sky-600',
                'textColor' => 'text-white',
                'icon' => 'twitter',
            ],
            'linkedin' => [
                'name' => 'linkedin',
                'label' => 'Continue with LinkedIn',
                'bgColor' => 'bg-blue-700 hover:bg-blue-800',
                'textColor' => 'text-white',
                'icon' => 'linkedin',
            ],
        ];

        return array_filter($providers, function ($provider) {
            return $this->hasCredentials($provider['name']);
        });
    }

    /**
     * Check if a provider has credentials configured.
     */
    private function hasCredentials(string $provider): bool
    {
        $config = config("services.{$provider}");

        return isset($config['client_id']) &&
               isset($config['client_secret']) &&
               ! empty($config['client_id']) &&
               ! empty($config['client_secret']);
    }
}
