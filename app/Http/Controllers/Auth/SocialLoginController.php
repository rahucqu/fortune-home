<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialLoginController extends Controller
{
    /**
     * Supported social providers.
     */
    private const SUPPORTED_PROVIDERS = ['google', 'github', 'facebook', 'twitter', 'linkedin'];

    /**
     * Redirect the user to the social provider authentication page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle social provider callback.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            return redirect()->route('login')->withErrors([
                'social' => 'Invalid state parameter. Please try again.',
            ]);
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'social' => 'Authentication failed. Please try again.',
            ]);
        }

        $user = $this->findOrCreateUser($socialUser, $provider);

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Find existing user or create new one.
     */
    private function findOrCreateUser(\Laravel\Socialite\Contracts\User $socialUser, string $provider): User
    {
        // First, try to find user by provider ID
        $existingUser = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existingUser) {
            // Update user info in case it changed
            $existingUser->update([
                'name' => $socialUser->getName() ?? $existingUser->name,
                'avatar' => $socialUser->getAvatar() ?? $existingUser->avatar,
            ]);

            return $existingUser;
        }

        // Try to find user by email
        $existingUserByEmail = User::where('email', $socialUser->getEmail())->first();

        if ($existingUserByEmail) {
            // Link social account to existing user
            $existingUserByEmail->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar() ?? $existingUserByEmail->avatar,
            ]);

            return $existingUserByEmail;
        }

        // Create new user
        return User::create([
            'name' => $socialUser->getName() ?? 'Unknown User',
            'email' => $socialUser->getEmail(),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'password' => Hash::make(Str::random(24)), // Random password for social users
            'email_verified_at' => now(), // Social providers verify emails
        ]);
    }

    /**
     * Unlink social provider from user account.
     */
    public function unlink(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $user = Auth::user();

        if (! $user->isSocialUser() || $user->provider !== $provider) {
            return back()->withErrors([
                'social' => 'No linked account found for this provider.',
            ]);
        }

        // Check if user has a password, if not, prevent unlinking
        if (! $user->password) {
            return back()->withErrors([
                'social' => 'You must set a password before unlinking your social account.',
            ]);
        }

        $user->update([
            'provider' => null,
            'provider_id' => null,
            'provider_token_expires_at' => null,
            'provider_refresh_token' => null,
        ]);

        return back()->with('status', 'Social account unlinked successfully.');
    }

    /**
     * Validate that the provider is supported.
     */
    private function validateProvider(string $provider): void
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS)) {
            abort(404, 'Unsupported social provider.');
        }
    }
}
