<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        try {
            $user = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Failed to authenticate with ' . ucfirst($provider)]);
        }

        // Check if we have a user with this email
        $authUser = User::where('email', $user->getEmail())->first();

        if ($authUser) {
            // Update existing user with provider details if not set
            if (empty($authUser->provider)) {
                $authUser->update([
                    'provider' => $provider,
                    'provider_id' => $user->getId(),
                    'provider_token' => $user->token,
                    'provider_refresh_token' => $user->refreshToken,
                ]);
            }
        } else {
            // Create a new user
            $authUser = User::create([
                'name' => $user->getName() ?? $user->getNickname(),
                'email' => $user->getEmail(),
                'password' => bcrypt(Str::random(16)), // Random password
                'provider' => $provider,
                'provider_id' => $user->getId(),
                'provider_token' => $user->token,
                'provider_refresh_token' => $user->refreshToken,
                'email_verified_at' => now(),
                'avatar' => $user->getAvatar(),
            ]);

            // Assign default role (you can modify this as needed)
            $authUser->assignRole('user');
        }

        // Log the user in
        Auth::login($authUser, true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Validate the provider from the callback.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['github', 'google', 'apple'])) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Please login using GitHub, Google, or Apple.']);
        }

        return null;
    }
}
