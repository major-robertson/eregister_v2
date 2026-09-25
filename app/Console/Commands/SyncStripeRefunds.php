<?php

namespace App\Console\Commands;

use App\Services\StripeRefundRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Stripe\StripeClient;

/**
 * Catches our payments up with refunds made in Stripe: the one-off backfill
 * for refunds from before the charge.refunded webhook, and a safety net if
 * an event is ever missed. Safe to run again.
 */
class SyncStripeRefunds extends Command
{
    protected $signature = 'payments:sync-refunds
        {--since=2026-01-01 : Only refunds created on or after this date}
        {--dry-run : Show what would change without saving anything}';

    protected $description = 'Record refunds made in Stripe on our payments';

    public function handle(StripeRefundRecorder $recorder): int
    {
        $stripe = new StripeClient(config('cashier.secret'));

        $refunds = $stripe->refunds->all([
            'limit' => 100,
            'created' => ['gte' => Carbon::parse($this->option('since'))->startOfDay()->timestamp],
        ])->autoPagingIterator();

        $dryRun = (bool) $this->option('dry-run');
        $result = $recorder->sync($refunds, $dryRun);

        $this->table(['Payment', 'Amount', 'Refunded', $dryRun ? 'Would be' : 'Result'], $result['rows']);
        $this->line("Refunds on charges that aren't ours (the old TaxResaleCertificate app): {$result['not_ours']}");

        if ($dryRun) {
            $this->warn('Dry run: nothing was saved.');
        }

        return self::SUCCESS;
    }
}
