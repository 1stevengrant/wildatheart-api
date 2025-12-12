<?php

namespace App\Providers;

use Auth0\SDK\Auth0;
use Auth0\SDK\Token;
use Illuminate\Http\Request;
use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Auth;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\InvalidTokenException;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    public function register(): void
    {
        $this->app->singleton(Auth0::class, function () {
            $config = config('auth0');

            if (empty($config['audience'])) {
                throw new \RuntimeException('AUTH0_AUDIENCE environment variable is not set');
            }

            return new Auth0(new SdkConfiguration([...$config, 'strategy' => 'api']));
        });

        parent::register();
    }

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Auth::viaRequest('auth0', function (Request $request) {
            /** @var Auth0 $auth0 */
            $auth0 = app(Auth0::class);

            if (! $request->headers->has('authorization')) {
                return null;
            }

            try {
                [$_, $token] = explode(' ', $request->headers->get('Authorization'));
                $decoded = $auth0->decode($token,
                    config('auth0.audience'),
                    null,
                    null,
                    null,
                    null,
                    null,
                    Token::TYPE_ACCESS_TOKEN)->toArray();
            } catch (InvalidTokenException $e) {
                return null;
            }

            return new GenericUser([...$decoded, 'id' => $decoded['sub'] ?? null]);
        });
    }
}
