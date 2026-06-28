<?php

namespace App\Providers;

use App\Domains\Esign\SignableResolver;
use App\Domains\Lien\Documents\DemandLetterGenerator;
use App\Domains\Lien\Documents\DemandLetterSignedGenerator;
use App\Domains\Lien\Esign\DemandLetterSignable;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class EsignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The composition root for signable document types. Each domain's
        // adapter is wired here; the factory closures defer instantiation, so
        // the generic Esign code never depends on a concrete domain at boot.
        $this->app->singleton(SignableResolver::class, function () {
            $resolver = new SignableResolver;

            $resolver->register('lien_filing', fn (LienFiling $filing): DemandLetterSignable => new DemandLetterSignable(
                $filing,
                $this->app->make(DemandLetterGenerator::class),
                $this->app->make(DemandLetterSignedGenerator::class),
            ));

            return $resolver;
        });

        $this->commands([
            \App\Domains\Esign\Console\VerifyEsignChain::class,
        ]);
    }

    public function boot(): void
    {
        Livewire::component('esign.sign-consent', \App\Domains\Esign\Livewire\SignConsent::class);
        Livewire::component('esign.sign-review', \App\Domains\Esign\Livewire\SignReview::class);
        Livewire::component('esign.sign-done', \App\Domains\Esign\Livewire\SignDone::class);
    }
}
