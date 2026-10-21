<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Seo\NoticeOfIntentStatePage;
use App\Support\Seo\States;
use App\Support\Seo\Text;

/**
 * What a state needs before a mechanics lien (EREG-143), in one place for the
 * product chooser, the wizard's first step and the wizard's notice-of-intent
 * question (EREG-138):
 *
 * - a notice of intent where database/data/lien_variants/notice_of_intent.php
 *   says 'required' (every claimant) or 'required_some' (some claimants);
 * - a preliminary notice where lien_state_rules.pre_notice_required says one
 *   keeps lien rights (LienStateGuide's prelim_required), and, on a project,
 *   from this claimant (everyone, or those who did not contract with the
 *   owner), as the wizard's state questions read it (WizardFields).
 *
 * Each prerequisite is one amber sentence. Liens only an attorney can file
 * (config lien.attorney_referral_states) are the caller's to leave out.
 */
final class LienPrerequisites
{
    /** Project steps that are done, under way or not this claimant's: no sentence for them. */
    private const SETTLED = [
        DeadlineStatus::Completed,
        DeadlineStatus::Purchased,
        DeadlineStatus::InFulfillment,
        DeadlineStatus::AwaitingClient,
        DeadlineStatus::AwaitingEsign,
        DeadlineStatus::AwaitingNotary,
        DeadlineStatus::Mailed,
        DeadlineStatus::Recorded,
        DeadlineStatus::NotApplicable,
    ];

    /**
     * The state's notice-of-intent entry when it requires one before the
     * lien, from every claimant or from some. Null otherwise.
     *
     * @return array<string, mixed>|null
     */
    public static function noiRequirement(string $state): ?array
    {
        $entry = NoticeOfIntentStatePage::entries()[strtoupper($state)] ?? null;

        return in_array($entry['status'] ?? null, ['required', 'required_some'], true) ? $entry : null;
    }

    /**
     * Whether the state requires a preliminary notice to keep lien rights;
     * with a project, whether it requires one from this claimant.
     */
    public static function prelimRequired(string $state, ?LienProject $project = null): bool
    {
        $rule = LienStateRule::query()->find(strtoupper($state));

        if (! $rule?->pre_notice_required) {
            return false;
        }

        return $project === null
            || $rule->pre_notice_required_for === 'everyone'
            || $project->contractedWithOwner() !== true;
    }

    /**
     * "Colorado requires a notice of intent at least 10 days before the lien
     * statement is filed. Order it first if you haven't."
     *
     * @param  array<string, mixed>  $entry  noiRequirement()
     */
    public static function noiSentence(string $state, array $entry): string
    {
        $name = self::stateName($state);
        $timing = lcfirst(rtrim((string) $entry['timing'], '.'));
        $from = $entry['status'] === 'required_some' ? ' from some claimants' : '';

        return "{$name} requires a notice of intent{$from} {$timing}. Order it first if you haven't.";
    }

    /**
     * "Florida requires a Notice to Owner from subcontractors and suppliers to
     * keep lien rights. Send it first if you haven't." Without a project the
     * sentence says who must send it; on a project the claimant is one of them.
     */
    public static function prelimSentence(string $state, bool $forProject = false): string
    {
        $name = self::stateName($state);
        $notice = self::prelimNoticeName($state);
        $from = $forProject ? '' : match (LienStateRule::query()->find(strtoupper($state))?->pre_notice_required_for) {
            'everyone' => '',
            'subs, subsubs, suppliers' => ' from subcontractors and suppliers',
            'subsubs, suppliers' => ' from sub-subcontractors and suppliers',
            default => ' from some claimants',
        };

        return "{$name} requires ".Text::article($notice)." {$notice}{$from} to keep lien rights. Send it first if you haven't.";
    }

    /**
     * The state's name for its preliminary notice, as the document we
     * prepare is titled ("Notice to Owner" in Florida), without the state's
     * own name in front; "preliminary notice" where the state has no
     * document data or uses the plain name.
     */
    public static function prelimNoticeName(string $state): string
    {
        $state = strtoupper($state);

        if (! is_file(database_path('data/lien_documents/'.strtolower($state).'.php'))) {
            return 'preliminary notice';
        }

        $title = (string) (LienDocumentRegistry::for($state)['kinds']['prelim_notice']['title'] ?? '');
        $title = trim((string) preg_replace('/^'.preg_quote(self::stateName($state), '/').'\s+/', '', $title));

        return $title === '' || strcasecmp($title, 'Preliminary Notice') === 0 ? 'preliminary notice' : $title;
    }

    /**
     * The state's prerequisites for a mechanics lien, notice of intent first.
     *
     * @return list<array{kind: string, sentence: string}>
     */
    public static function forState(string $state): array
    {
        $items = [];

        if (($entry = self::noiRequirement($state)) !== null) {
            $items[] = ['kind' => 'noi', 'sentence' => self::noiSentence($state, $entry)];
        }

        if (self::prelimRequired($state)) {
            $items[] = ['kind' => 'prelim_notice', 'sentence' => self::prelimSentence($state)];
        }

        return $items;
    }

    /**
     * The prerequisites for a mechanics lien on this project that are still
     * open, each with the link that starts it on the project (null when the
     * project has no step for it). A step that is done, paid for, under way
     * or not this claimant's (StepStatusCalculator) has no sentence.
     *
     * @return list<array{kind: string, sentence: string, url: ?string, label: string}>
     */
    public static function forProject(LienProject $project): array
    {
        $state = strtoupper((string) $project->jobsite_state);
        $items = [];

        if (($entry = self::noiRequirement($state)) !== null) {
            $items[] = ['kind' => 'noi', 'sentence' => self::noiSentence($state, $entry), 'label' => 'Order the notice of intent'];
        }

        if (self::prelimRequired($state, $project)) {
            $items[] = ['kind' => 'prelim_notice', 'sentence' => self::prelimSentence($state, forProject: true), 'label' => 'Order the '.self::prelimNoticeName($state)];
        }

        $open = [];

        foreach ($items as $item) {
            $deadline = $project->getDeadlineForType($item['kind']);

            if ($deadline !== null && in_array(app(StepStatusCalculator::class)->forDeadline($deadline)->status, self::SETTLED, true)) {
                continue;
            }

            $open[] = $item + [
                'url' => $deadline ? route('lien.filings.start', ['project' => $project, 'deadline' => $deadline]) : null,
            ];
        }

        return $open;
    }

    private static function stateName(string $state): string
    {
        return States::name($state) ?? config('states.'.strtoupper($state), strtoupper($state));
    }
}
