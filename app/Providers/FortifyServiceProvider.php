<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Domains\Business\Models\BusinessInvitation;
use App\Http\Middleware\ActivateMarketingLeadContext;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use App\Http\Responses\VerifyEmailResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(VerifyEmailResponseContract::class, VerifyEmailResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(function () {
            $this->captureLandingPath();

            // Activate marketing lead context from ?lead= or cookie
            app(ActivateMarketingLeadContext::class)->handle(
                request(),
                fn () => response('')
            );

            return view('pages::auth.register', [
                'invitation' => $this->pendingBusinessInvitation(),
            ]);
        });
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * The business invitation the guest arrived from, if any — stashed in the
     * session by the invitation landing page so registration can prefill the
     * invited email and explain why they're here.
     */
    private function pendingBusinessInvitation(): ?BusinessInvitation
    {
        $id = session('pending_business_invitation_id');

        if (! $id) {
            return null;
        }

        $invitation = BusinessInvitation::with('business')->find($id);

        return ($invitation && ! $invitation->isExpired()) ? $invitation : null;
    }

    /**
     * Capture the landing path and URL when the user visits the register page.
     */
    private function captureLandingPath(): void
    {
        $referer = request()->header('referer');

        if (! $referer) {
            return;
        }

        $refererPath = parse_url($referer, PHP_URL_PATH);
        $refererHost = parse_url($referer, PHP_URL_HOST);
        $currentHost = request()->getHost();

        // Only capture internal landing paths (from our own domain)
        if ($refererHost === $currentHost && $refererPath && $refererPath !== '/register') {
            session()->put('signup_landing_path', $refererPath);
            session()->put('signup_landing_url', $referer);
        }
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
