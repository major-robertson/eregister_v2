<?php

namespace App\Console\Commands;

use App\Domains\SalesTax\Reports\FunnelReport;
use App\Mail\SalesTaxFunnelReportMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Weekly sales tax funnel numbers (sign-ups → drafts → paid → submitted,
 * plus paid registrations by Ads campaign) with a link to the Google Ads
 * campaign table for the same dates. Prints the table; --email sends it to
 * the admins (or --to addresses). Scheduled Monday mornings in routes/console.php.
 */
class SalesTaxFunnelReport extends Command
{
    protected $signature = 'report:sales-tax-funnel
        {--days=7 : Length of the period in whole Eastern days, today excluded}
        {--email : Email the report instead of only printing it}
        {--to=* : Send to these addresses instead of the admin users}';

    protected $description = 'Sales tax registration funnel for the last period and the period before it';

    public function handle(FunnelReport $report): int
    {
        $data = $report->build(max(1, (int) $this->option('days')));

        $this->line(sprintf(
            '%s to %s vs %s to %s',
            $data['current']['start']->format('M j'),
            $data['current']['end']->subDay()->format('M j'),
            $data['previous']['start']->format('M j'),
            $data['previous']['end']->subDay()->format('M j'),
        ));

        $this->table(
            ['Metric', 'This period', 'Previous'],
            collect(FunnelReport::labels())->map(fn (string $label, string $key) => [
                $label,
                $data['current']['metrics'][$key],
                $data['previous']['metrics'][$key],
            ])->values()->all(),
        );

        if ($data['by_campaign'] !== []) {
            $this->table(
                ['Ads campaign', 'Paid registrations'],
                collect($data['by_campaign'])->map(fn (int $count, string $campaign) => [$campaign, $count])->values()->all(),
            );
        }

        if (! $this->option('email')) {
            return self::SUCCESS;
        }

        $recipients = $this->recipients();

        if ($recipients === []) {
            $this->warn('No recipients: no admin users and no --to addresses.');

            return self::FAILURE;
        }

        Mail::to($recipients)->queue(new SalesTaxFunnelReportMail($data));

        $this->info('Report queued to '.implode(', ', $recipients));

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function recipients(): array
    {
        $to = array_values(array_filter((array) $this->option('to')));

        if ($to !== []) {
            return $to;
        }

        return User::role('admin')->pluck('email')->filter()->values()->all();
    }
}
