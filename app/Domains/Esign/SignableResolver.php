<?php

namespace App\Domains\Esign;

use App\Domains\Esign\Contracts\Signable;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Maps a signable Eloquent model (by its morph alias) to its Signable adapter.
 * Domains register a factory at boot; the generic Esign actions resolve through
 * here so they never reference a concrete domain model.
 */
class SignableResolver
{
    /** @var array<string, callable(Model): Signable> */
    private array $factories = [];

    /**
     * @param  callable(Model): Signable  $factory
     */
    public function register(string $morphAlias, callable $factory): void
    {
        $this->factories[$morphAlias] = $factory;
    }

    public function for(Model $model): Signable
    {
        $alias = $model->getMorphClass();

        if (! isset($this->factories[$alias])) {
            throw new RuntimeException("No Signable adapter registered for [{$alias}].");
        }

        return ($this->factories[$alias])($model);
    }

    public function forRequest(SignatureRequest $request): Signable
    {
        return $this->for($request->signable);
    }

    public function supports(string $morphAlias): bool
    {
        return isset($this->factories[$morphAlias]);
    }
}
