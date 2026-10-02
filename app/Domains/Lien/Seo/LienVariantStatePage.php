<?php

namespace App\Domains\Lien\Seo;

use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Support\Seo\States;
use App\Support\Seo\Text;
use App\Support\Seo\Urls;

/**
 * Shared shape of the per-state pages for one lien document
 * ("/liens/notice-of-intent-to-lien/{state}", "/liens/lien-release/{state}").
 *
 * Each page joins three sources: the researched entry in
 * database/data/lien_variants/{file}.php (the statute's rule in words, with
 * cites), the state's lien rule page (deadline and filing office wording,
 * so the two pages can never disagree) and, for states with a
 * lien_documents data file, the document the filing product prepares
 * (its title, statute, recipients and the free blank PDF).
 */
abstract class LienVariantStatePage
{
    /** The ten states with a notice-of-intent rule row, then the ten busiest. */
    public const STATES = ['AR', 'CO', 'KY', 'LA', 'MD', 'MO', 'NV', 'NJ', 'ND', 'TN', 'TX', 'CA', 'FL', 'NY', 'GA', 'NC', 'AZ', 'WA', 'PA', 'IL'];

    /** Bump when the shape of these objects or the page copy changes: cached instances are replaced on the next request. */
    public const CACHE_VERSION = 1;

    /** The LienDocumentRegistry kind this page is about. */
    public const KIND = '';

    /** The data file under database/data/lien_variants, without ".php". */
    public const DATA_FILE = '';

    /** The page's route name; the blank PDF's is "{ROUTE}.blank". */
    public const ROUTE = '';

    public readonly string $name;

    public readonly string $slug;

    /** "a" or "an", for "an Arizona lien" / "a Texas lien". */
    public readonly string $article;

    /**
     * The document's entry in LienDocumentRegistry, only for states with a
     * lien_documents data file (the generic defaults are not state facts).
     *
     * @var array<string, mixed>|null
     */
    public readonly ?array $document;

    /** The title of the free blank PDF, or null when the state has none. */
    public readonly ?string $blankTitle;

    /** @param array<string, mixed> $entry */
    final protected function __construct(
        public readonly string $code,
        public readonly LienStatePage $lien,
        public readonly array $entry,
    ) {
        $this->name = $lien->name;
        $this->slug = $lien->slug;
        $this->article = Text::article($this->name);
        $this->document = is_file(database_path('data/lien_documents/'.strtolower($code).'.php'))
            ? LienDocumentRegistry::for($code)['kinds'][static::KIND]
            : null;
        $this->blankTitle = app(BlankLienDocument::class)->form($code, static::KIND)?->title;
    }

    /** @var array<string, array<string, array<string, mixed>>> keyed by class */
    private static array $entries = [];

    /**
     * The states with a page, code => name, by name.
     *
     * @return array<string, string>
     */
    public static function availableStates(): array
    {
        $states = array_intersect_key(States::names(), array_flip(array_keys(static::entries())));
        asort($states);

        return $states;
    }

    public static function has(string $code): bool
    {
        return isset(static::entries()[strtoupper($code)]);
    }

    public static function forCode(string $code): ?static
    {
        $code = strtoupper($code);
        $entry = static::entries()[$code] ?? null;
        $lien = $entry === null ? null : LienStatePage::forCode($code);

        return $lien === null ? null : new static($code, $lien, $entry);
    }

    public static function cacheKey(string $code): string
    {
        // The page carries a LienStatePage, so its version is part of the key too.
        return 'seo.lien-'.str_replace('_', '-', static::KIND).'-state.v'.self::CACHE_VERSION.'-'.LienStatePage::CACHE_VERSION.'.'.strtoupper($code);
    }

    public static function dataFile(): string
    {
        return database_path('data/lien_variants/'.static::DATA_FILE.'.php');
    }

    /** @return array<string, array<string, mixed>> code => researched entry, for the twenty states */
    public static function entries(): array
    {
        return self::$entries[static::class] ??= array_intersect_key(require static::dataFile(), array_flip(self::STATES));
    }

    /** Used by tests to force re-reads of the data files. */
    public static function flush(): void
    {
        self::$entries = [];
    }

    public static function urlFor(string $code): string
    {
        return Urls::absolute(route(static::ROUTE, ['state' => States::slug((string) States::name($code))], absolute: false));
    }

    public function url(): string
    {
        return static::urlFor($this->code);
    }

    public function blankUrl(): ?string
    {
        return $this->blankTitle === null ? null : route(static::ROUTE.'.blank', ['state' => $this->slug]);
    }

    abstract public function title(): string;

    abstract public function metaDescription(): string;

    /** @return array<int, array{label: string, value: string, detail?: string}> */
    abstract public function keyFacts(): array;

    /** @return array<int, array{q: string, a: string}> */
    abstract public function faq(): array;

    /* ------------------------------------------------------------- pricing */

    public function selfServePrice(): int
    {
        return $this->price('self_serve');
    }

    public function fullServicePrice(): int
    {
        return $this->price('full_service');
    }

    private function price(string $level): int
    {
        $cents = config('lien.state_pricing.'.$this->code.'.'.static::KIND.'.'.$level)
            ?? config('lien.pricing.'.static::KIND.'.'.$level);

        return (int) round(((int) $cents) / 100);
    }

    /* --------------------------------------------------------------- shared */

    /**
     * The state's lien deadline in the lien page's own words: "The Colorado
     * lien deadline is within 4 months after last furnishing labor or
     * materials." The table on the lien page has the detail when it varies.
     */
    public function lienDeadlineSentence(): ?string
    {
        $headline = $this->lien->headlineLienDeadline();
        if ($headline === null) {
            return null;
        }

        $sentence = "The {$this->name} lien deadline is ".preg_replace('/\s*\(varies by .*?\)$/', '', $headline).'.';

        return match ($this->lien->lienDeadlineVariance()) {
            'project type' => $sentence.' It varies by project type.',
            'role' => $sentence.' It varies by role and project type.',
            default => $sentence,
        };
    }

    /**
     * Bordering states that have a page of this kind, padded from the
     * alphabetical neighbours: code => name.
     *
     * @return array<string, string>
     */
    public function nearby(): array
    {
        return States::bordering($this->code, 4, static::availableStates());
    }

    /** Meta description: the lead, then the first of the extra sentences that still fits in 160 characters. */
    protected static function fit(string $lead, string ...$extras): string
    {
        $text = $lead;
        foreach ($extras as $extra) {
            if (mb_strlen($text.' '.$extra) <= 160) {
                $text .= ' '.$extra;

                break;
            }
        }

        return $text;
    }
}
