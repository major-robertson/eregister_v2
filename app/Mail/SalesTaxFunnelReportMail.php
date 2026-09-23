<?php

namespace App\Mail;

use App\Domains\SalesTax\Reports\FunnelReport;
use App\Support\Analytics\AdsTableState;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The weekly sales tax funnel report (see FunnelReport), sent to the admins.
 */
class SalesTaxFunnelReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array{days: int, current: array{start: \Carbon\CarbonImmutable, end: \Carbon\CarbonImmutable, metrics: array<string, int>}, previous: array{start: \Carbon\CarbonImmutable, end: \Carbon\CarbonImmutable, metrics: array<string, int>}, by_campaign: array<string, int>}  $report
     */
    public function __construct(public array $report) {}

    public function envelope(): Envelope
    {
        $current = $this->report['current'];

        return new Envelope(subject: sprintf(
            'Sales tax funnel %s to %s: %d paid, $%s',
            $current['start']->format('M j'),
            $current['end']->subDay()->format('M j'),
            $current['metrics']['paid'],
            number_format($current['metrics']['revenue']),
        ));
    }

    public function content(): Content
    {
        $current = $this->report['current'];
        $previous = $this->report['previous'];

        return new Content(
            markdown: 'mail.sales-tax-funnel-report',
            with: [
                'periodLabel' => $current['start']->format('M j').' to '.$current['end']->subDay()->format('M j'),
                'previousLabel' => $previous['start']->format('M j').' to '.$previous['end']->subDay()->format('M j'),
                'rows' => collect(FunnelReport::labels())->map(fn (string $label, string $key) => [
                    'label' => trim($label),
                    'current' => $current['metrics'][$key],
                    'previous' => $previous['metrics'][$key],
                ])->values()->all(),
                'byCampaign' => $this->report['by_campaign'],
                'adsUrl' => AdsTableState::campaignsUrl($current['start'], $current['end']->subDay()),
                'previousAdsUrl' => AdsTableState::campaignsUrl($previous['start'], $previous['end']->subDay()),
            ],
        );
    }
}
