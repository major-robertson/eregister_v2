<?php

namespace App\Domains\Lien\Seo;

use App\Support\Seo\States;
use Illuminate\Support\Facades\Cache;

/**
 * One row per state for the 50-state lien guides (mechanics lien deadlines,
 * preliminary notice requirements). Every value comes from the same cached
 * LienStatePage objects the "/liens/{state}" pages render, so a guide can
 * never disagree with a state page. The assembled rows are cached for a day.
 *
 * The rows hold text and flags only, never raw rule codes, and no URLs: the
 * views build links with route() so a tunnel host still works.
 */
final class LienStateGuide
{
    /** Bump when the row shape changes. LienStatePage::CACHE_VERSION is part of the key too. */
    public const CACHE_VERSION = 1;

    /** Recipients that are an office or registry rather than the owner. */
    private const NON_OWNER_RECIPIENTS = ['lien_agent', 'state_registry', 'parish_recorder'];

    public static function cacheKey(): string
    {
        return 'seo.lien-state-guide.v'.LienStatePage::CACHE_VERSION.'.'.self::CACHE_VERSION;
    }

    /**
     * @return array<string, array{
     *     code: string, name: string, slug: string, attorney: bool,
     *     lien: ?string, lien_rows: array<int, array{who: string, scope: string, when: string}>, variance: ?string,
     *     prelim_required: bool, prelim_everyone: bool, prelim_summary: string,
     *     prelim_deadline: array<int, string>, prelim_rows: array<int, array{who: string, scope: string, when: string}>,
     *     prelim_to: ?string, prelim_to_owner: bool, prelim_how: ?string,
     *     noi_required: bool, noi: string, enforcement: ?string,
     *     statute: ?string, statute_url: ?string
     * }> code => row, sorted by state name
     */
    public static function rows(): array
    {
        return Cache::remember(self::cacheKey(), now()->addDay(), function () {
            $rows = [];
            foreach (States::names() as $code => $name) {
                $page = Cache::remember(LienStatePage::cacheKey($code), now()->addDay(), fn () => LienStatePage::forCode($code));
                if ($page) {
                    $rows[$code] = self::row($page);
                }
            }
            uasort($rows, fn (array $a, array $b) => strcmp($a['name'], $b['name']));

            return $rows;
        });
    }

    /** @return array<string, mixed> */
    private static function row(LienStatePage $page): array
    {
        $rule = $page->rule;
        $required = (bool) $rule->pre_notice_required;
        $prelimRows = $page->deadlines['prelim_notice'] ?? [];
        $noiDays = $rule->noi_lead_time_days;

        return [
            'code' => $page->code,
            'name' => $page->name,
            'slug' => $page->slug,
            'attorney' => $page->requiresAttorney(),
            'lien' => $page->headlineLienDeadline(),
            'lien_rows' => $page->deadlines['mechanics_lien'] ?? [],
            'variance' => $page->lienDeadlineVariance(),
            'prelim_required' => $required,
            'prelim_everyone' => $required && $rule->pre_notice_required_for === 'everyone',
            'prelim_summary' => $page->prelimSummary(),
            'prelim_deadline' => self::prelimDeadline($required, $prelimRows),
            'prelim_rows' => $required ? $prelimRows : [],
            'prelim_to' => $required ? $page->prelimRecipientsLabel() : null,
            'prelim_to_owner' => $required && ! in_array($rule->prelim_recipients, self::NON_OWNER_RECIPIENTS, true),
            'prelim_how' => $required ? $page->prelimDeliveryLabel() : null,
            'noi_required' => $page->noiSentence() !== null,
            'noi' => match (true) {
                $noiDays === null => 'Not required',
                $noiDays > 0 => "Required, at least {$noiDays} days before filing",
                default => 'Required before filing',
            },
            'enforcement' => self::shortEnforcement($page->enforcementSentence()),
            'statute' => $page->statutes[0] ?? null,
            'statute_url' => $rule->statute_url ?: null,
        ];
    }

    /**
     * The preliminary notice deadline as table lines: the one deadline when
     * it applies to every claimant on every project, otherwise one line per
     * claimant group ("General contractors: within 15 days after ...").
     *
     * @param  array<int, array{who: string, scope: string, when: string}>  $rows
     * @return array<int, string>
     */
    private static function prelimDeadline(bool $required, array $rows): array
    {
        if (! $required) {
            return ['No preliminary notice'];
        }
        if (! $rows) {
            return ['See the state page'];
        }
        if (count($rows) === 1 && $rows[0]['who'] === 'All claimants' && $rows[0]['scope'] === 'All projects') {
            return [ucfirst($rows[0]['when'])];
        }

        return array_map(fn (array $row) => $row['who']
            .($row['scope'] !== 'All projects' ? ' ('.lcfirst($row['scope']).')' : '')
            .': '.$row['when'], $rows);
    }

    /** "within 6 months after the lien is recorded" => "6 months after recording". */
    private static function shortEnforcement(?string $sentence): ?string
    {
        if ($sentence === null) {
            return null;
        }

        $short = strtr(preg_replace('/^within /', '', $sentence), [
            'after the lien is recorded' => 'after recording',
            'after last furnishing labor or materials' => 'after last furnishing',
            'after project completion' => 'after completion',
            'after the last day the lien could have been filed' => 'after the last day to file the lien',
        ]);

        return ucfirst($short);
    }

    /* ------------------------------------------------- data-derived answers */

    /**
     * States grouped by their shortest "within N days/months" lien deadline,
     * shortest first. Deadlines the table states another way (Texas counts to
     * a day of a later month) are left out of the ranking.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @return array<int, array{text: string, states: array<int, string>}> each group's deadline text and "Name (qualifier)" labels
     */
    public static function rankLienDeadlines(array $rows): array
    {
        return self::rank($rows, 'lien_rows', '/^within (\d+) (days?|months?) after/');
    }

    /**
     * Same ranking for the preliminary notice, counting only deadlines that
     * run from first furnishing.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @return array<int, array{text: string, states: array<int, string>}>
     */
    public static function rankPrelimDeadlines(array $rows): array
    {
        return self::rank($rows, 'prelim_rows', '/^within (\d+) (days?|months?) after first furnishing/');
    }

    /** @return array<int, array{text: string, states: array<int, string>}> */
    private static function rank(array $rows, string $key, string $pattern): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $best = null;
            foreach ($row[$key] as $deadline) {
                if (! preg_match($pattern, $deadline['when'], $m)) {
                    continue;
                }
                $amount = (int) $m[1];
                $days = str_starts_with($m[2], 'month') ? $amount * 30.4 : $amount;
                if ($best === null || $days < $best['days']) {
                    $best = ['days' => $days, 'amount' => $amount.' '.$m[2], 'row' => $deadline];
                }
            }
            if ($best === null) {
                continue;
            }

            $qualifier = match (true) {
                $best['row']['scope'] !== 'All projects' => lcfirst($best['row']['scope']),
                $best['row']['who'] !== 'All claimants' => lcfirst($best['row']['who']),
                default => null,
            };
            $groupKey = (string) $best['days'];
            $groups[$groupKey]['days'] = $best['days'];
            $groups[$groupKey]['text'] = $best['amount'];
            $groups[$groupKey]['states'][] = $row['name'].($qualifier ? " ({$qualifier})" : '');
        }

        usort($groups, fn (array $a, array $b) => $a['days'] <=> $b['days']);

        return array_map(fn (array $group) => ['text' => $group['text'], 'states' => $group['states']], $groups);
    }

    /**
     * The four FAQs on the mechanics lien deadlines guide, worded from the rows.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @return array<int, array{q: string, a: string}>
     */
    public static function deadlineFaq(array $rows): array
    {
        $items = [];

        $ranked = self::rankLienDeadlines($rows);
        if ($ranked) {
            $first = $ranked[0];
            $answer = 'Among deadlines counted in days or months, '.self::listNames($first['states'])
                .(count($first['states']) === 1 ? ' has' : ' have')." the shortest: {$first['text']}.";
            if ($second = $ranked[1] ?? null) {
                $answer .= ' '.self::listNames($second['states'])." come next, at {$second['text']}.";
            }
            $items[] = [
                'q' => 'Which states have the shortest mechanics lien deadline?',
                'a' => $answer.' The count starts from a different event in different states, so check the table for the trigger.',
            ];
        }

        $everyone = array_column(array_filter($rows, fn (array $row) => $row['prelim_everyone']), 'name');
        $items[] = [
            'q' => 'Which states require a preliminary notice from every claimant?',
            'a' => 'In '.count($everyone).' states, every claimant must send a preliminary notice, including the general contractor: '
                .self::listNames($everyone).'. Elsewhere the notice covers only some claimants, or is not required at all.',
        ];

        $noi = array_column(array_filter($rows, fn (array $row) => $row['noi_required']), 'name');
        $items[] = [
            'q' => 'Which states require a notice of intent to lien?',
            'a' => self::listNames($noi).' require a notice of intent before the lien is recorded. The lead time differs by state, and the table shows it. In the other '
                .(count($rows) - count($noi)).' states it is not required, although sending one is often what gets the invoice paid.',
        ];

        $uniform = count(array_filter($rows, fn (array $row) => $row['variance'] === null));
        $byType = array_column(array_filter($rows, fn (array $row) => $row['variance'] === 'project type'), 'name');
        $byRole = array_column(array_filter($rows, fn (array $row) => $row['variance'] === 'role'), 'name');
        $items[] = [
            'q' => 'Does the lien deadline depend on my role or the project type?',
            'a' => "In {$uniform} states, one deadline applies to every claimant on every project."
                .($byType ? ' The deadline depends on the project type in '.self::listNames($byType).'.' : '')
                .($byRole ? ' It depends on your role in '.self::listNames($byRole).'.' : '')
                .' Each state page lists the full schedule.',
        ];

        return $items;
    }

    /**
     * The four FAQs on the preliminary notice guide, worded from the rows.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @return array<int, array{q: string, a: string}>
     */
    public static function prelimFaq(array $rows): array
    {
        $required = array_filter($rows, fn (array $row) => $row['prelim_required']);
        $items = [];

        $none = array_column(array_filter($rows, fn (array $row) => ! $row['prelim_required']), 'name');
        $items[] = [
            'q' => 'Which states do not require a preliminary notice?',
            'a' => 'In '.count($none).' states, no preliminary notice is required to preserve lien rights: '.self::listNames($none)
                .'. Sending one anyway can still help you get paid, and some of these states require a notice of intent before the lien.',
        ];

        $ranked = self::rankPrelimDeadlines($rows);
        if ($ranked) {
            $first = $ranked[0];
            $answer = 'Among deadlines counted from first furnishing, '.self::listNames($first['states'])
                .(count($first['states']) === 1 ? ' has' : ' have')." the shortest: {$first['text']}.";
            if ($second = $ranked[1] ?? null) {
                $answer .= ' '.self::listNames($second['states'])." come next, at {$second['text']}.";
            }
            $items[] = [
                'q' => 'Which states have the shortest preliminary notice deadline?',
                'a' => $answer.' Some states set the deadline another way, such as before work starts, so check the table for your state.',
            ];
        }

        $methods = [];
        foreach ($required as $row) {
            if ($row['prelim_how'] && $row['prelim_how'] !== 'any method that proves receipt') {
                $methods[$row['prelim_how']][] = $row['name'];
            }
        }
        $sentences = array_map(
            fn (string $method, array $names) => self::listNames($names).(count($names) === 1 ? ' calls' : ' call')." for {$method}.",
            array_keys($methods),
            $methods,
        );
        $items[] = [
            'q' => 'How must a preliminary notice be delivered?',
            'a' => 'It depends on the state. '.implode(' ', $sentences)
                .' In the other states that require a notice, any method that proves receipt is accepted. Keep proof of delivery either way.',
        ];

        $elsewhere = array_filter($required, fn (array $row) => ! $row['prelim_to_owner'] && $row['prelim_to']);
        $places = array_map(fn (array $row) => "in {$row['name']} it goes to {$row['prelim_to']}", array_values($elsewhere));
        $items[] = [
            'q' => 'Does the notice always go to the property owner?',
            'a' => $places
                ? 'No. '.ucfirst(self::listNames($places)).'. In the other states that require a notice, it goes to the property owner, and in many of them to the general contractor or lender as well.'
                : 'Yes. In every state that requires a notice, it goes to the property owner, and in many of them to the general contractor or lender as well.',
        ];

        return $items;
    }

    /** "Alabama, Alaska and Arizona" */
    public static function listNames(array $names): string
    {
        $names = array_values($names);
        if (count($names) <= 2) {
            return implode(' and ', $names);
        }

        return implode(', ', array_slice($names, 0, -1)).' and '.end($names);
    }
}
