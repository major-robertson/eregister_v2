<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Seo\LienStatePage;
use App\Domains\Lien\Seo\NoticeOfIntentStatePage;
use App\Domains\Lien\Services\CountyRecordingFee;
use App\Domains\Lien\Support\LienPrerequisites;
use App\Domains\Lien\Support\LienProducts;
use App\Domains\Lien\Support\RecordingTurnaround;
use App\Domains\Lien\Support\ServiceComparison;
use App\Support\Seo\Text;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * "What do you need to happen?" (EREG-143): six cards in the customer's
 * words, each saying what the product does and does not do. With a state
 * chosen (a logged-in customer starts on their latest project's state), each
 * card adds the state's deadline rule in one sentence and its price; the
 * mechanics lien card names what the state needs first (a notice of intent,
 * a preliminary notice); a document we don't file there says so and points
 * to an attorney instead of a dead end. "Start" goes through
 * LienStartController into sign-up or the project flow.
 *
 * On the pricing page ($compare) a table under the cards compares self-serve
 * and full service for one document in the chosen state.
 */
class LienProductChooser extends Component
{
    /** The state the cards describe ("TX"), or '' for none. */
    public string $state = '';

    /** Show the self-serve and full-service table under the cards. */
    public bool $compare = false;

    /** The document the table compares. */
    public string $document = 'mechanics_lien';

    public function mount(bool $compare = false): void
    {
        $this->compare = $compare;
        $this->state = $this->defaultState() ?? '';
    }

    public function updatedState(): void
    {
        $this->state = $this->validState();
    }

    public function updatedDocument(): void
    {
        if (! LienProducts::isOrderable($this->document)) {
            $this->document = 'mechanics_lien';
        }
    }

    /**
     * A logged-in customer's state: their latest project's jobsite, else
     * the business address. Null for visitors.
     */
    private function defaultState(): ?string
    {
        $business = Auth::user()?->currentBusiness();

        if ($business === null) {
            return null;
        }

        $state = $business->lienProjects()->latest('id')->value('jobsite_state')
            ?: ($business->business_address['state'] ?? null);
        $state = strtoupper((string) $state);

        return array_key_exists($state, config('states', [])) ? $state : null;
    }

    private function validState(): string
    {
        $state = strtoupper(trim($this->state));

        return array_key_exists($state, config('states', [])) ? $state : '';
    }

    public function render(): View
    {
        $state = $this->validState();
        $page = $state === '' ? null : Cache::remember(LienStatePage::cacheKey($state), now()->addDay(), fn () => LienStatePage::forCode($state));
        $hasRule = $page !== null || ($state !== '' && LienStateRule::query()->whereKey($state)->exists());
        $document = LienProducts::isOrderable($this->document) ? $this->document : 'mechanics_lien';

        $cards = [];

        foreach (LienProducts::CARDS as $key => $card) {
            $cards[$key] = $this->card($key, $card, $state, $page, $hasRule);
        }

        return view('livewire.lien.product-chooser', [
            'cards' => $cards,
            'states' => config('states', []),
            'stateName' => $state === '' ? null : config("states.{$state}"),
            'orderable' => collect(LienProducts::ORDERABLE)->mapWithKeys(fn (string $key) => [$key => LienProducts::name($key)])->all(),
            'comparison' => $this->compare && $state !== '' && $cards[$document]['not_served'] === null
                ? ['rows' => ServiceComparison::rows($document, $state), 'prices' => ServiceComparison::prices($document, $state)]
                : null,
            'comparisonNotServed' => $this->compare && $state !== '' ? $cards[$document]['not_served'] : null,
        ]);
    }

    /**
     * @param  array{need: string, name: string, does: string, does_not: string}  $card
     * @return array<string, mixed>
     */
    private function card(string $key, array $card, string $state, ?LienStatePage $page, bool $hasRule): array
    {
        $card += [
            'key' => $key,
            'deadline' => null,
            'prices' => null,
            'turnaround' => null,
            'prerequisites' => [],
            'not_served' => null,
            'start_url' => null,
            'talk_url' => null,
        ];

        if ($key === LienProducts::BOND_CLAIM) {
            return array_replace($card, ['talk_url' => route('contact')]);
        }

        if ($state !== '' && ($notServed = $this->notServed($key, $state, $hasRule)) !== null) {
            return array_replace($card, ['not_served' => $notServed, 'talk_url' => route('contact')]);
        }

        $prices = ServiceComparison::prices($key, $state);

        $card = array_replace($card, [
            'prices' => array_map(fn (int $cents) => '$'.number_format($cents / 100), $prices),
            'start_url' => route('liens.start', array_filter(['product' => $key, 'state' => $state])),
        ]);

        if ($state === '') {
            return $card;
        }

        return array_replace($card, [
            'deadline' => $this->deadline($key, $state, $page),
            'turnaround' => in_array($key, CountyRecordingFee::KINDS, true) ? RecordingTurnaround::sentence($state) : null,
            'prerequisites' => $key === 'mechanics_lien' ? $this->prerequisites($state) : [],
        ]);
    }

    /**
     * "We don't file mechanics liens in Hawaii. We can connect you with a
     * Hawaii attorney who specializes in construction lien filings." for a
     * lien only an attorney can file (config lien.attorney_referral_states),
     * and for every document in a state without lien rules; the wizard's
     * referral screen says the same.
     */
    private function notServed(string $key, string $state, bool $hasRule): ?string
    {
        $referral = $key === 'mechanics_lien' && in_array($state, config('lien.attorney_referral_states', []), true);

        if ($hasRule && ! $referral) {
            return null;
        }

        $name = (string) config("states.{$state}", $state);

        return "We don't file ".LienProducts::plural($key)." in {$name}. We can connect you with ".Text::article($name)." {$name} attorney who specializes in construction lien filings.";
    }

    /** The state's deadline rule for the product, in one sentence. */
    private function deadline(string $key, string $state, ?LienStatePage $page): ?string
    {
        $name = (string) config("states.{$state}", $state);

        return match ($key) {
            'prelim_notice' => match (true) {
                ! $page?->rule->pre_notice_required => "{$name} does not require one to keep lien rights.",
                ($headline = $page->headlineDeadline('prelim_notice')) !== null => "Deadline in {$name}: {$headline}.",
                default => "In {$name}: ".lcfirst($page->prelimSummary()).'.',
            },
            'noi' => $this->noiDeadline($state, $name, $page),
            'mechanics_lien' => ($headline = $page?->headlineLienDeadline()) !== null
                ? "Deadline in {$name}: {$headline}."
                : 'We work out the deadline from your project dates.',
            'demand_letter' => 'No deadline. You can send one at any time.',
            'lien_release' => 'Record it once you have been paid.',
            default => null,
        };
    }

    /**
     * The notice of intent's timing from the researched entry
     * (notice_of_intent.php) where the state has one, since some states'
     * rule rows count the wrong way (EREG-91); otherwise the rule row.
     */
    private function noiDeadline(string $state, string $name, ?LienStatePage $page): string
    {
        $entry = NoticeOfIntentStatePage::entries()[$state] ?? null;

        if ($entry !== null && LienPrerequisites::noiRequirement($state) !== null) {
            return "Deadline in {$name}: ".lcfirst(rtrim((string) $entry['timing'], '.')).'.';
        }

        if ($entry === null && ($sentence = $page?->noiSentence()) !== null) {
            return $sentence;
        }

        return "{$name} does not require one. You can still send one before you file.";
    }

    /**
     * What the state needs before a mechanics lien, each with the Start link
     * of the document it names.
     *
     * @return list<array{sentence: string, url: string, label: string}>
     */
    private function prerequisites(string $state): array
    {
        $notice = LienPrerequisites::prelimNoticeName($state);

        return array_map(fn (array $item) => [
            'sentence' => $item['sentence'],
            'url' => route('liens.start', ['product' => $item['kind'], 'state' => $state]),
            'label' => $item['kind'] === 'noi' ? 'Start a notice of intent' : 'Start '.Text::article($notice).' '.$notice,
        ], LienPrerequisites::forState($state));
    }
}
