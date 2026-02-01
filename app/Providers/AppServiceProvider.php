<?php

namespace App\Providers;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\ConditionEvaluator;
use App\Domains\Forms\Engine\Validation\CrossFieldValidatorRegistry;
use App\Domains\Forms\Engine\Validation\Rules\OwnershipTotals100;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Policies\LienFilingPolicy;
use App\Domains\Lien\Policies\LienProjectPolicy;
use App\Domains\Portal\Policies\BusinessPolicy;
use App\Domains\Portal\Policies\FormApplicationPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        // Bind ConditionEvaluator as transient (non-singleton) to prevent state leaking
        $this->app->bind(ConditionEvaluator::class);

        // Register CrossFieldValidatorRegistry as singleton
        $this->app->singleton(CrossFieldValidatorRegistry::class, function () {
            $registry = new CrossFieldValidatorRegistry;
            $registry->register(new OwnershipTotals100);

            return $registry;
        });
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
    }

    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
            'business' => \App\Domains\Business\Models\Business::class,
            'lien_filing' => \App\Domains\Lien\Models\LienFiling::class,
            // Future:
            // 'llc_filing' => \App\Domains\Llc\Models\LlcFiling::class,
            // 'subscription' => \App\Models\Subscription::class,
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

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(8)->letters()
            : null
        );
    }
}
