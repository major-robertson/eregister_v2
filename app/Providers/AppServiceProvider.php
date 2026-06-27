<?php

namespace App\Providers;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\ConditionEvaluator;
use App\Domains\Forms\Engine\Validation\CrossFieldValidatorRegistry;
use App\Domains\Forms\Engine\Validation\Rules\LocationsPrincipalUniqueAndMatchesBusinessAddress;
use App\Domains\Forms\Engine\Validation\Rules\OwnershipTotals100;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Policies\LienFilingPolicy;
use App\Domains\Lien\Policies\LienProjectPolicy;
use App\Domains\Portal\Policies\BusinessPolicy;
use App\Domains\Portal\Policies\FormApplicationPolicy;
use App\Support\Workspaces\WorkspaceRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register domain console commands
        $this->commands([
            \App\Domains\Lien\Console\SendDeadlineReminders::class,
        ]);

        // Bind ConditionEvaluator as transient (non-singleton) to prevent state leaking
        $this->app->bind(ConditionEvaluator::class);

        // Register CrossFieldValidatorRegistry as singleton
        $this->app->singleton(CrossFieldValidatorRegistry::class, function () {
            $registry = new CrossFieldValidatorRegistry;
            $registry->register(new OwnershipTotals100);
            $registry->register(new LocationsPrincipalUniqueAndMatchesBusinessAddress);

            return $registry;
        });

        // Register the workspace registry as a singleton so the cached
        // Workspace DTOs are reused for the lifetime of the request.
        $this->app->singleton(WorkspaceRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureMorphMap();
        $this->configureDefaults();
        $this->configurePolicies();
        $this->configureLivewire();
        $this->configureTunnelScheme();
    }

    /**
     * When the app is served locally through an HTTPS dev tunnel (Expose /
     * Herd Share), the tunnel terminates TLS upstream and forwards to Herd over
     * plain HTTP with an `X-Forwarded-Proto: https` header. Force the https
     * scheme so generated asset/URL links use https and aren't blocked as
     * mixed content on the public tunnel page.
     *
     * Local only, and we deliberately do NOT trust proxy IP/host headers:
     * reading the forwarded-proto header here changes only URL generation, so
     * it adds none of the rate-limit-bypass / host-header-poisoning surface
     * that `trustProxies('*')` would introduce on the live Forge site.
     */
    protected function configureTunnelScheme(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if ($this->app->environment('local')
            && request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
    }

    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
            'business' => \App\Domains\Business\Models\Business::class,
            'lien_filing' => \App\Domains\Lien\Models\LienFiling::class,
            'lien_project' => \App\Domains\Lien\Models\LienProject::class,
            'form_application' => \App\Domains\Forms\Models\FormApplication::class,
            'email_sequence' => \App\Models\EmailSequence::class,
            'payment' => \App\Models\Payment::class,
        ]);
    }

    protected function configureLivewire(): void
    {
        // Register Livewire components from Domains directory
        Livewire::component('business.business-dropdown', \App\Domains\Business\Livewire\BusinessDropdown::class);
        Livewire::component('business.business-switcher', \App\Domains\Business\Livewire\BusinessSwitcher::class);
        Livewire::component('business.onboarding-wizard', \App\Domains\Business\Livewire\OnboardingWizard::class);
        Livewire::component('billing.checkout', \App\Domains\Billing\Livewire\Checkout::class);
        Livewire::component('forms.state-selector', \App\Domains\Forms\Livewire\StateSelector::class);
        Livewire::component('forms.multi-state-form-runner', \App\Domains\Forms\Livewire\MultiStateFormRunner::class);

        // Lien domain components
        Livewire::component('lien.project-list', \App\Domains\Lien\Livewire\ProjectList::class);
        Livewire::component('lien.project-form', \App\Domains\Lien\Livewire\ProjectForm::class);
        Livewire::component('lien.project-show', \App\Domains\Lien\Livewire\ProjectShow::class);
        Livewire::component('lien.party-manager', \App\Domains\Lien\Livewire\PartyManager::class);
        Livewire::component('lien.filing-wizard', \App\Domains\Lien\Livewire\FilingWizard::class);
        Livewire::component('lien.filing-show', \App\Domains\Lien\Livewire\FilingShow::class);
        Livewire::component('lien.filing-checkout', \App\Domains\Lien\Livewire\FilingCheckout::class);

        // Lien admin components
        Livewire::component('lien.admin.board', \App\Domains\Lien\Admin\Livewire\LienBoard::class);
        Livewire::component('lien.admin.filing-detail', \App\Domains\Lien\Admin\Livewire\LienFilingDetail::class);

        // Sales Tax domain components
        Livewire::component('sales-tax.dashboard', \App\Domains\SalesTax\Livewire\Dashboard::class);
        Livewire::component('sales-tax.registration-checkout', \App\Domains\SalesTax\Livewire\RegistrationCheckout::class);

        // Forms admin components (sales tax kanban + detail)
        Livewire::component('forms.admin.sales-tax-board', \App\Domains\Forms\Admin\Livewire\SalesTaxBoard::class);
        Livewire::component('forms.admin.sales-tax-board-all', \App\Domains\Forms\Admin\Livewire\SalesTaxBoardAll::class);
        Livewire::component('forms.admin.sales-tax-application-state-detail', \App\Domains\Forms\Admin\Livewire\SalesTaxApplicationStateDetail::class);

        // Formations domain components
        Livewire::component('formations.dashboard', \App\Domains\Formations\Livewire\Dashboard::class);

        // Marketing domain components
        Livewire::component('marketing.contractor-landing', \App\Domains\Marketing\Livewire\ContractorLanding::class);
    }

    protected function configurePolicies(): void
    {
        Gate::policy(Business::class, BusinessPolicy::class);
        Gate::policy(FormApplication::class, FormApplicationPolicy::class);
        Gate::policy(LienProject::class, LienProjectPolicy::class);
        Gate::policy(LienFiling::class, LienFilingPolicy::class);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // Timestamps are stored in UTC (config app.timezone). The whole product
        // displays times in Eastern, so `->eastern()` is the one place that
        // converts for display. Use it before ->format() at every user/admin-facing
        // timestamp. Must be a real closure (not an arrow fn) so Carbon can rebind
        // $this to the date instance; registered on both Carbon flavors.
        $eastern = function () {
            return $this->setTimezone(config('app.display_timezone'));
        };
        CarbonImmutable::macro('eastern', $eastern);
        \Carbon\Carbon::macro('eastern', $eastern);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(8)->letters()
            : null
        );
    }
}
