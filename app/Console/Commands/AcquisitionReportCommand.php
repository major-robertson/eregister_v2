<?php

namespace App\Console\Commands;

use App\Domains\Admin\Reports\AcquisitionReport;
use Illuminate\Console\Command;

/**
 * Read-only: prints sign-ups, paying businesses and lifetime revenue by
 * first-touch channel and landing section per Eastern month (the matrix on
 * the admin stats dashboard), plus the top landing paths. Run on production
 * for the SEO baseline: php artisan report:acquisition --months=12
 */
class AcquisitionReportCommand extends Command
{
    protected $signature = 'report:acquisition
        {--months=12 : Number of Eastern months to cover, this month included}
        {--json : Print JSON instead of tables}';

    protected $description = 'Sign-ups, paying businesses and revenue by first-touch channel and landing page, per month (read-only)';

    public function handle(AcquisitionReport $report): int
    {
        $months = max(1, (int) $this->option('months'));
        $matrix = $report->matrix($months);
        $topPaths = $report->topLandingPaths($months);

        if ($this->option('json')) {
            $this->line(json_encode([
                'since' => $report->windowStart($months)->toDateString(),
                'months' => $months,
                'matrix' => $matrix,
                'top_landing_paths' => $topPaths,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->line('Sign-ups since '.$report->windowStart($months)->format('M j, Y').' (Eastern). Revenue is each business\'s lifetime succeeded payments.');

        $this->table(
            ['Month', 'Channel', 'Section', 'Signups', 'Paid businesses', 'Revenue', 'Conversion'],
            array_map(fn (array $row) => [
                $row['month'],
                $row['channel'],
                $row['section'] ?? '',
                $row['signups'],
                $row['paying_businesses'],
                $this->dollars($row['revenue_cents']),
                number_format($row['conversion_rate'], 1).'%',
            ], $matrix),
        );

        $this->table(
            ['Landing path', 'Signups', 'Paid businesses', 'Revenue', 'Conversion'],
            array_map(fn (array $row) => [
                $row['landing_path'],
                $row['signups'],
                $row['paying_businesses'],
                $this->dollars($row['revenue_cents']),
                number_format($row['conversion_rate'], 1).'%',
            ], $topPaths),
        );

        return self::SUCCESS;
    }

    private function dollars(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}
