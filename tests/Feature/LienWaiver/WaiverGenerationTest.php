<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\Actions\GenerateWaiver;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\PdfBuilder;

beforeEach(function () {
    Storage::fake('s3');
});

if (! function_exists('waiverGenFixture')) {
    /** A TX conditional-progress waiver on a real project with known names. */
    function waiverGenFixture(array $overrides = []): LienWaiver
    {
        $business = Business::factory()->create(['name' => 'Acme Construction LLC']);
        $project = LienProject::factory()->forBusiness($business)->inState('TX')->create([
            'name' => 'Water Tower Rehab',
            'jobsite_county' => 'Travis County',
        ]);

        return LienWaiver::factory()->forProject($project)->create(array_merge([
            'kind' => WaiverKind::ConditionalProgress,
            'amount_cents' => 123_456,
            'through_date' => '2026-06-30',
            'counterparty_company' => 'Vendor Co LLC',
            'counterparty_name' => 'Vera Vendor',
        ], $overrides));
    }
}

if (! function_exists('waiverGenStubbedAction')) {
    /**
     * GenerateWaiver wired to a generator whose PDF step is stubbed; DOMPDF
     * is slow, so only the one dedicated end-to-end test renders for real.
     */
    function waiverGenStubbedAction(): GenerateWaiver
    {
        $generator = new class(app(WaiverFormResolver::class)) extends WaiverGenerator
        {
            public function renderFromSnapshot(array $payload): PdfBuilder
            {
                return new class extends PdfBuilder
                {
                    public function generatePdfContent(): string
                    {
                        return '%PDF-1.7 stub-lien-waiver';
                    }
                };
            }
        };

        return new GenerateWaiver($generator);
    }
}

describe('GenerateWaiver action', function () {
    it('freezes the snapshot and stamps template, version, status, and the generated media', function () {
        $waiver = waiverGenFixture();

        $generated = waiverGenStubbedAction()->execute($waiver);

        expect($generated->status)->toBe(WaiverStatus::Generated);
        expect($generated->generated_at)->not->toBeNull();
        expect($generated->template_key)->toBe('documents.lien.waivers.bodies.tx-conditional-progress');
        expect($generated->template_version)->toBe(1);

        $snapshot = $generated->render_snapshot_json;
        expect($snapshot)->toBeArray();
        expect($snapshot['form']['template'])->toBe('documents.lien.waivers.bodies.tx-conditional-progress');
        expect($snapshot['form']['title'])->toBe('Conditional Waiver and Release on Progress Payment');
        expect($snapshot['form']['state'])->toBe('TX');
        expect($snapshot['form']['state_name'])->toBe('Texas');
        expect($snapshot['form']['kind'])->toBe('conditional_progress');
        expect($snapshot['amount'])->toBe('1,234.56');
        expect($snapshot['through_date'])->toBe('June 30, 2026');
        expect($snapshot['project']['name'])->toBe('Water Tower Rehab');
        expect($snapshot['project']['county'])->toBe('Travis County');
        expect($snapshot['claimant']['company'])->toBe('Acme Construction LLC');
        expect($snapshot['customer']['company'])->toBe('Vendor Co LLC');

        $media = $generated->getMedia('generated');
        expect($media)->toHaveCount(1);
        expect($media->first()->file_name)
            ->toBe("lien-waiver-tx-conditional-progress-{$generated->public_id}.pdf");
        expect(Storage::disk('s3')->exists($media->first()->getPathRelativeToRoot()))->toBeTrue();
    });

    it('keeps the frozen snapshot when the project changes after generation', function () {
        $waiver = waiverGenFixture();
        $generated = waiverGenStubbedAction()->execute($waiver);

        $generated->project->update(['name' => 'Renamed After Generation']);

        expect($generated->refresh()->render_snapshot_json['project']['name'])
            ->toBe('Water Tower Rehab');
    });

    it('regeneration replaces the previous unsigned PDF and refreshes the snapshot', function () {
        $waiver = waiverGenFixture();
        $action = waiverGenStubbedAction();

        $generated = $action->execute($waiver);
        $firstMedia = $generated->getFirstMedia('generated');
        expect($firstMedia)->not->toBeNull();

        $generated->update(['amount_cents' => 99_900]);
        $regenerated = $action->execute($generated);

        $media = $regenerated->getMedia('generated');
        expect($media)->toHaveCount(1);
        expect($media->first()->id)->not->toBe($firstMedia->id);
        expect($regenerated->render_snapshot_json['amount'])->toBe('999.00');
        expect($regenerated->status)->toBe(WaiverStatus::Generated);
    });
});

describe('WaiverGenerator payload', function () {
    it('provide direction puts the business on the claimant line and the counterparty as customer', function () {
        $waiver = waiverGenFixture(['direction' => WaiverDirection::Provide]);

        $data = app(WaiverGenerator::class)->data($waiver);

        expect($data['claimant']['company'])->toBe('Acme Construction LLC');
        expect($data['customer']['company'])->toBe('Vendor Co LLC');
        expect($data['customer']['name'])->toBe('Vera Vendor');
        expect($data['customer']['email'])->toBe($waiver->counterparty_email);

        // The signer block travels with whoever is waiving rights.
        expect($data['signer']['company'])->toBe('Acme Construction LLC');
    });

    it('collect direction swaps them: the counterparty vendor is the claimant', function () {
        $waiver = waiverGenFixture([
            'direction' => WaiverDirection::Collect,
            'signer_name' => null,
        ]);

        $data = app(WaiverGenerator::class)->data($waiver);

        expect($data['claimant']['company'])->toBe('Vendor Co LLC');
        expect($data['claimant']['name'])->toBe('Vera Vendor');
        expect($data['customer']['company'])->toBe('Acme Construction LLC');

        expect($data['signer']['company'])->toBe('Vendor Co LLC');
        // Without an explicit signer, collect defaults to the counterparty contact.
        expect($data['signer']['name'])->toBe('Vera Vendor');
    });

    it('builds the filename from state, kind, and public id', function () {
        $waiver = waiverGenFixture();
        $generator = app(WaiverGenerator::class);

        expect($generator->filename($waiver))
            ->toBe("lien-waiver-tx-conditional-progress-{$waiver->public_id}.pdf");

        // Without a public id (the wizard's unsaved free-download path) a
        // random lowercase reference is used instead.
        $waiver->public_id = null;
        expect($generator->filename($waiver))
            ->toMatch('/^lien-waiver-tx-conditional-progress-[0-9a-z]{8}\.pdf$/');
    });
});

describe('real PDF render', function () {
    // The single DOMPDF render in this file; everything else stubs the PDF step.
    it('generates a real TX conditional progress PDF end to end', function () {
        $waiver = waiverGenFixture();

        $generated = app(GenerateWaiver::class)->execute($waiver);

        $media = $generated->getFirstMedia('generated');
        expect($media)->not->toBeNull();

        $bytes = Storage::disk('s3')->get($media->getPathRelativeToRoot());
        expect(str_starts_with($bytes, '%PDF'))->toBeTrue();
        expect(strlen($bytes))->toBeGreaterThan(1_000);
    });
});
