<?php

use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Services\RushTier;
use Carbon\CarbonImmutable;

uses(Tests\TestCase::class);

/** Tuesday, October 20, 2026 in New York; no holidays through October 30. */
function rushToday(): CarbonImmutable
{
    return CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York');
}

/** An unsaved filing; its deadline is Friday October 30 unless given. */
function rushTierFiling(string $slug, string $serviceLevel, ?string $mustSubmitBy, ?string $deadline = '2026-10-30'): LienFiling
{
    $filing = new LienFiling([
        'service_level' => $serviceLevel,
        'must_submit_by' => $mustSubmitBy,
    ]);

    return $filing->setRelation('documentType', new LienDocumentType(['slug' => $slug]))
        ->setRelation('projectDeadline', $deadline ? (new LienProjectDeadline)->forceFill(['due_date' => $deadline]) : null);
}

describe('tier boundaries', function () {
    it('maps business days left to a tier', function (int $days, ?string $tier) {
        expect(RushTier::forBusinessDaysLeft($days))->toBe($tier);
    })->with([
        '-2' => [-2, 'expedite'],
        '0' => [0, 'expedite'],
        '1' => [1, 'expedite'],
        '3' => [3, 'expedite'],
        '4' => [4, 'rush'],
        '6' => [6, 'rush'],
        '7' => [7, null],
    ]);

    it('counts business days from today to the submit-by date', function (string $mustSubmitBy, ?string $tier) {
        // The deadline, Friday October 30, is still ahead.
        expect(RushTier::for('mechanics_lien', 'full_service', CarbonImmutable::parse($mustSubmitBy), CarbonImmutable::parse('2026-10-30'), rushToday()))->toBe($tier);
    })->with([
        'two business days gone' => ['2026-10-16', 'expedite'],
        'yesterday' => ['2026-10-19', 'expedite'],
        'today, 0 days' => ['2026-10-20', 'expedite'],
        'Wednesday, 1 day' => ['2026-10-21', 'expedite'],
        'Friday, 3 days' => ['2026-10-23', 'expedite'],
        'Monday, 4 days' => ['2026-10-26', 'rush'],
        'Wednesday, 6 days' => ['2026-10-28', 'rush'],
        'Thursday, 7 days' => ['2026-10-29', null],
    ]);

    it('charges nothing once the deadline itself has passed', function () {
        $submitBy = CarbonImmutable::parse('2026-10-16');

        expect(RushTier::for('mechanics_lien', 'full_service', $submitBy, CarbonImmutable::parse('2026-10-19'), rushToday()))->toBeNull()
            ->and(RushTier::for('mechanics_lien', 'full_service', $submitBy, CarbonImmutable::parse('2026-10-20'), rushToday()))->toBe('expedite')
            ->and(RushTier::forFiling(rushTierFiling('mechanics_lien', 'full_service', '2026-10-16', '2026-10-19'), rushToday()))->toBeNull()
            ->and(RushTier::forFiling(rushTierFiling('mechanics_lien', 'full_service', '2026-10-16', null), rushToday()))->toBe('expedite');
    });

    it('reads the ranges from config', function () {
        config(['lien.rush_tiers' => [
            'rush' => ['min_days' => 5, 'max_days' => 10],
            'expedite' => ['min_days' => 1, 'max_days' => 4],
        ]]);

        expect(RushTier::forBusinessDaysLeft(4))->toBe('expedite')
            ->and(RushTier::forBusinessDaysLeft(0))->toBe('expedite')
            ->and(RushTier::forBusinessDaysLeft(5))->toBe('rush')
            ->and(RushTier::forBusinessDaysLeft(10))->toBe('rush')
            ->and(RushTier::forBusinessDaysLeft(11))->toBeNull()
            ->and(RushTier::acknowledgement('mechanics_lien'))->toContain('With fewer than 5 business days left');
    });
});

describe('which orders pay one', function () {
    it('charges full-service mechanics liens', function () {
        expect(RushTier::forFiling(rushTierFiling('mechanics_lien', 'full_service', '2026-10-22'), rushToday()))->toBe('expedite')
            ->and(RushTier::forFiling(rushTierFiling('mechanics_lien', 'full_service', '2026-10-27'), rushToday()))->toBe('rush');
    });

    it('leaves notices alone until config turns them on', function (string $slug) {
        expect(RushTier::charges($slug))->toBeFalse()
            ->and(RushTier::forFiling(rushTierFiling($slug, 'full_service', '2026-10-22'), rushToday()))->toBeNull();

        config(['lien.rush_fee_documents' => ['mechanics_lien', 'prelim_notice', 'noi']]);

        expect(RushTier::charges($slug))->toBeTrue()
            ->and(RushTier::forFiling(rushTierFiling($slug, 'full_service', '2026-10-22'), rushToday()))->toBe('expedite')
            ->and(RushTier::forFiling(rushTierFiling($slug, 'full_service', '2026-10-27'), rushToday()))->toBe('rush');
    })->with(['prelim_notice', 'noi']);

    it('never charges documents without a deadline, even when listed', function () {
        config(['lien.rush_fee_documents' => ['mechanics_lien', 'demand_letter']]);

        expect(RushTier::charges('demand_letter'))->toBeFalse();
    });

    it('never charges a self-serve order', function () {
        expect(RushTier::forFiling(rushTierFiling('mechanics_lien', 'self_serve', '2026-10-22'), rushToday()))->toBeNull();
    });

    it('never charges an order without a submit-by date', function () {
        expect(RushTier::forFiling(rushTierFiling('mechanics_lien', 'full_service', null), rushToday()))->toBeNull();
    });

    it('never charges documents without a deadline', function (string $slug) {
        expect(RushTier::forFiling(rushTierFiling($slug, 'full_service', '2026-10-22'), rushToday()))->toBeNull();
    })->with(['demand_letter', 'lien_release']);

    it('uses today in New York by default', function () {
        // 11pm Monday October 19 in New York is already Tuesday in UTC.
        $this->travelTo(CarbonImmutable::parse('2026-10-19 23:00:00', 'America/New_York')->utc());

        // Friday the 23rd is 4 business days from Monday (rush), 3 from Tuesday (expedite).
        expect(RushTier::forFiling(rushTierFiling('mechanics_lien', 'full_service', '2026-10-23')))->toBe('rush');
    });
});

describe('copy', function () {
    it('says why the fee applies', function () {
        expect(RushTier::reason(2, 'mechanics_lien'))->toBe('2 business days left before we must submit to the county.')
            ->and(RushTier::reason(1, 'mechanics_lien'))->toBe('1 business day left before we must submit to the county.')
            ->and(RushTier::reason(0, 'mechanics_lien'))->toBe('No business days left before we must submit to the county.')
            ->and(RushTier::reason(-2, 'mechanics_lien'))->toBe('No business days left before we must submit to the county.')
            ->and(RushTier::reason(5, 'prelim_notice'))->toBe('5 business days left before we must send it.');
    });

    it('words the expedite terms', function () {
        expect(RushTier::acknowledgement('mechanics_lien'))
            ->toBe("We start today. With fewer than 4 business days left we can't promise the county records it in time, and the order is not refunded on that basis.")
            ->and(RushTier::acknowledgement('noi'))
            ->toBe("We start today. With fewer than 4 business days left we can't promise it is served in time, and the order is not refunded on that basis.");
    });

    it('labels the tiers and asks for terms on expedite only', function () {
        expect(RushTier::label('rush'))->toBe('Rush')
            ->and(RushTier::label('expedite'))->toBe('Expedite')
            ->and(RushTier::itemLabel('expedite'))->toBe('Expedite fee')
            ->and(RushTier::requiresAcknowledgement('expedite'))->toBeTrue()
            ->and(RushTier::requiresAcknowledgement('rush'))->toBeFalse()
            ->and(RushTier::requiresAcknowledgement(null))->toBeFalse();
    });
});
