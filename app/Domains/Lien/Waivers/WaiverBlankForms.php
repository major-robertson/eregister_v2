<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverKind;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ungated blank copies of the statutory waiver forms, for the marketing
 * pages. Each one is the generator's own document shell and body rendered
 * with every blank left empty (WaiverFormPreview::blankPayload), so the
 * download can never drift from what the wizard produces. Only states whose
 * law prescribes the wording get blanks; everywhere else the generator's
 * house form is the product.
 *
 * Rendered PDFs are cached on the local disk under a versioned key that also
 * carries the form's template_version and a hash of the body and shell views,
 * so a template change produces a new file instead of serving a stale one.
 */
class WaiverBlankForms
{
    /** Bump to invalidate every cached blank (e.g. after a payload change). */
    public const CACHE_VERSION = 1;

    public const CACHE_DIRECTORY = 'waiver-blanks';

    public function __construct(
        private WaiverFormResolver $resolver,
        private WaiverFormPreview $preview,
        private WaiverGenerator $generator,
    ) {}

    /**
     * The state's statutory forms, keyed by URL slug (conditional-progress).
     * Kinds that share one statutory body (Wyoming's single Lien Waiver)
     * are listed once. Missouri's only statutory form is the residential
     * unconditional final waiver, so the residential variant is resolved.
     *
     * @return array<string, array{kind: WaiverKind, title: string, form: ResolvedWaiverForm}>
     */
    public function forState(string $state): array
    {
        $code = strtoupper($state);

        if (! WaiverStateRegistry::isSupported($code)) {
            return [];
        }

        $forms = [];
        $templates = [];

        foreach ($this->resolver->availableKinds($code) as $entry) {
            if (! $entry['enabled']) {
                continue;
            }

            $form = $this->resolver->resolve($code, $entry['kind'], 'residential');

            if (str_contains($form->template, '.generic-') || in_array($form->template, $templates, true)) {
                continue;
            }

            $templates[] = $form->template;
            $forms[self::slug($entry['kind'])] = [
                'kind' => $entry['kind'],
                'title' => $form->title,
                'form' => $form,
            ];
        }

        return $forms;
    }

    /**
     * Every state with at least one statutory blank, in registry order.
     *
     * @return array<string, array<string, array{kind: WaiverKind, title: string, form: ResolvedWaiverForm}>>
     */
    public function all(): array
    {
        return collect(array_keys(WaiverStateRegistry::STATE_NAMES))
            ->mapWithKeys(fn (string $code) => [$code => $this->forState($code)])
            ->filter()
            ->all();
    }

    /**
     * The blank form's PDF bytes, or null when the state has no statutory
     * form for that slug.
     */
    public function pdf(string $state, string $slug): ?string
    {
        $entry = $this->forState($state)[$slug] ?? null;

        if ($entry === null) {
            return null;
        }

        $disk = Storage::disk('local');
        $path = $this->cachePath($entry['form'], $slug);

        if ($disk->exists($path)) {
            return $disk->get($path);
        }

        $bytes = $this->generator
            ->renderFromSnapshot($this->preview->blankPayload($entry['form'], $entry['kind']))
            ->generatePdfContent();

        $disk->put($path, $bytes);

        return $bytes;
    }

    public function filename(string $state, string $slug): string
    {
        $name = Str::slug(WaiverStateRegistry::STATE_NAMES[strtoupper($state)] ?? $state);

        return "{$name}-{$slug}-lien-waiver-blank.pdf";
    }

    public function cachePath(ResolvedWaiverForm $form, string $slug): string
    {
        $hash = substr(md5(
            $this->viewSource($form->template).$this->viewSource('documents.lien.waivers.shell')
        ), 0, 8);

        return sprintf(
            '%s/v%d/%s-%s-t%d-%s.pdf',
            self::CACHE_DIRECTORY,
            self::CACHE_VERSION,
            strtolower($form->state),
            $slug,
            $form->templateVersion,
            $hash,
        );
    }

    public static function slug(WaiverKind $kind): string
    {
        return str_replace('_', '-', $kind->value);
    }

    private function viewSource(string $view): string
    {
        $path = view()->getFinder()->find($view);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
