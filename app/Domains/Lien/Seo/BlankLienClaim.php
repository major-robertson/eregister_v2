<?php

namespace App\Domains\Lien\Seo;

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPayload;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Documents\LienDocumentResolver;
use App\Domains\Lien\Documents\LienDocumentUnavailable;
use App\Domains\Lien\Documents\ResolvedLienDocument;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Support\Seo\States;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Ungated blank copy of a state's mechanics lien instrument, for the
 * "/liens/{state}" pages. It is the generator's own instrument shell and the
 * state's body rendered from an unsaved filing with no parties, dates,
 * amounts or county, so every field prints as a ruled blank and the download
 * can never drift from what the filing product prepares. Only states with a
 * lien_documents data file (a state-specific instrument) get one; attorney
 * states and states without a file 404.
 *
 * Rendered PDFs are cached on the local disk under a versioned key that also
 * carries the instrument's template_version and a hash of the shell, the
 * body, the shared parts and clauses and the state's data file, so a template
 * or clause change produces a new file instead of serving a stale one.
 */
class BlankLienClaim
{
    /** Bump to invalidate every cached blank (e.g. after a payload change). */
    public const CACHE_VERSION = 1;

    public const CACHE_DIRECTORY = 'lien-claim-blanks';

    public function __construct(
        private LienDocumentResolver $resolver,
        private LienDocumentGenerator $generator,
    ) {}

    /** The state's resolved mechanics lien instrument, or null when there is no blank to offer. */
    public function form(string $state): ?ResolvedLienDocument
    {
        $code = strtoupper($state);

        if (! LienDocumentRegistry::isSupported($code) || ! is_file(self::dataFile($code))) {
            return null;
        }

        try {
            $form = $this->resolver->resolve(self::blankFiling($code), 'mechanics_lien');
        } catch (LienDocumentUnavailable) {
            return null;
        }

        return $form->isInstrument() ? $form : null;
    }

    public function available(string $state): bool
    {
        return $this->form($state) !== null;
    }

    /** The blank instrument's PDF bytes, or null when the state has none. */
    public function pdf(string $state): ?string
    {
        $form = $this->form($state);

        if ($form === null) {
            return null;
        }

        $disk = Storage::disk('local');
        $path = $this->cachePath($form);

        if ($disk->exists($path)) {
            return $disk->get($path);
        }

        $bytes = Pdf::view($this->generator->layout($form), ['doc' => $this->payload($form)])
            ->driver('dompdf')
            ->format('letter')
            ->generatePdfContent();

        $disk->put($path, $bytes);

        return $bytes;
    }

    /**
     * The payload the filing product builds, from an unsaved filing with
     * nothing filled in. No date, no preparer and no return-to address: a
     * blank form names nobody.
     *
     * @return array<string, mixed>
     */
    public function payload(ResolvedLienDocument $form): array
    {
        $payload = LienDocumentPayload::fromFiling(self::blankFiling($form->state), $form);

        // A ruled line where a body prints the date as text ("On ____, I, ...").
        $payload['date'] = str_repeat('_', 16);
        $payload['generated_at'] = 'blank form';
        $payload['preparer'] = ['name' => null, 'attention' => null, 'address_lines' => [], 'phone' => null, 'email' => null];

        return $payload;
    }

    /** "texas-affidavit-claiming-a-mechanics-lien-blank.pdf" */
    public function filename(ResolvedLienDocument $form): string
    {
        return Str::slug(States::name($form->state).' '.str_replace(["'", "\u{2019}"], '', $form->title)).'-blank.pdf';
    }

    public function cachePath(ResolvedLienDocument $form): string
    {
        $sources = [
            $this->viewSource($this->generator->layout($form)),
            $this->viewSource($form->body),
            (string) @file_get_contents(self::dataFile($form->state)),
        ];

        foreach (['documents/lien/_parts', 'documents/lien/instruments/clauses'] as $directory) {
            foreach (glob(resource_path('views/'.$directory.'/*.blade.php')) ?: [] as $file) {
                $sources[] = (string) file_get_contents($file);
            }
        }

        return sprintf(
            '%s/v%d/%s-t%d-%s.pdf',
            self::CACHE_DIRECTORY,
            self::CACHE_VERSION,
            strtolower($form->state),
            $form->templateVersion,
            substr(md5(implode("\n", $sources)), 0, 8),
        );
    }

    private static function dataFile(string $code): string
    {
        return database_path('data/lien_documents/'.strtolower($code).'.php');
    }

    /**
     * An unsaved filing on an unsaved project in the state, with its
     * relations preset to empty so building the payload reads nothing about
     * parties, the business or recipients.
     */
    private static function blankFiling(string $code): LienFiling
    {
        $project = new LienProject;
        $project->setRelation('parties', collect());
        $project->setRelation('business', null);

        $filing = (new LienFiling)->forceFill(['jurisdiction_state' => $code]);
        $filing->setRelation('project', $project);
        $filing->setRelation('recipients', collect());

        return $filing;
    }

    private function viewSource(string $view): string
    {
        $path = view()->getFinder()->find($view);

        return is_file($path) ? (string) file_get_contents($path) : '';
    }
}
